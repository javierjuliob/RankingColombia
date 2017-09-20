<?php

class SupsysticTables_Tables_Module extends SupsysticTables_Core_BaseModule
{
	/**
	 * Data for render table with single selected cell
	 */
	protected $isSingleCell = array();
	/**
	 * Data for loading tables' rows from history
	 */
    protected $isFromHistory = array();

	protected $checkSpreadsheet = false;

	/**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        $this->registerShortcode();
        $this->registerValueShortcode();
        $this->registerCellShortcode();
        $this->registerTwigTableRender();
        $this->registerMenuItem();
        $this->addTwigHighlighter();

        $this->cacheDirectory = $this->getConfig()->get('plugin_cache_tables');

        if ($this->isPluginPage()) {
            $this->reviewNoticeCheck();
        }
        add_action('widgets_init', array($this, 'registerWidget'));
		add_action('shutdown', array($this, 'onShutdown'));
        $dispatcher = $this->getEnvironment()->getDispatcher();
        $dispatcher->on('after_tables_loaded', array($this, 'onAfterLoaded'));
		$this->renderTableHistorySection();
    }

    /**
     * Renders the table
     * @param int $id
     * @return string
     */
    public function render($id)
    {
        if($this->disallowIndexing($id)) {
            return;
        }
        $cachePath = $this->cacheDirectory . DIRECTORY_SEPARATOR . $id;

        if (!$this->isSingleCell && file_exists($cachePath) && $this->getEnvironment()->isProd()) {
            return file_get_contents($cachePath);
        }
		$environment = $this->getEnvironment();
		$twig = $environment->getTwig();
		$core = $environment->getModule('core');				// @var SupsysticTables_Core_Module $core
		$tables = $core->getModelsFactory()->get('tables');		// @var SupsysticTables_Tables_Model_Tables $tables
		$table = $tables->getById($id);

	    if (!$table) {
            return sprintf($environment->translate('The table with ID %d not exists.'), $id);
        }
		if ($this->checkSpreadsheet
			&& $environment->isPro()
			&& isset($table->settings['features']['import']['google']['automatically_update'])
			&& isset($table->settings['features']['import']['google']['link'])
		) {
			try {
				$this->getEnvironment()->getModule('importer')->autoUpdateTableFromGoogle($id, $table);
			} catch(Exception $e) {
				return $e->getMessage();
			}
			// We need to get the new rows' data from db
			$table = $tables->getById($id);
		}
        if (isset($table->meta['columnsWidth'])) {
            $columnsTotalWidth = array_sum($table->meta['columnsWidth']);

            if($columnsTotalWidth) {
				foreach ($table->meta['columnsWidth'] as &$value) {
					$value = round($value / $columnsTotalWidth * 100, 4);
				}
			}
        }
		if($this->isSingleCell) {
			// Unset unneded elements and features
			unset($table->settings['elements']['head']);
			unset($table->settings['elements']['foot']);
			unset($table->settings['elements']['caption']);
			unset($table->settings['features']['ordering']);
			unset($table->settings['features']['paging']);
			unset($table->settings['features']['searching']);
			unset($table->settings['features']['after_table_loaded_script']);

			$table->meta['css'] = $table->meta['css'] .
				'#supsystic-table-' . $table->view_id . ' #supsystic-table-' . $id . ' { margin-left: 0; }' .
				'#supsystic-table-' . $table->view_id . ' #supsystic-table-' . $id . ',
				#supsystic-table-' . $table->view_id . ' #supsystic-table-' . $id . ' th,
				#supsystic-table-' . $table->view_id . ' #supsystic-table-' . $id . ' td { width: auto !important; min-width: 100px; }';

			foreach($table->rows as $key => $row) {
				if ($this->isSingleCell['row'] === $key + 1) {
					foreach($row['cells'] as $index => $cell) {
						if($index == $this->isSingleCell['col'] - 1) {
							// For correct work of saving data through editable fields
							$table->rows[$key]['cells'][$index]['row'] = $this->isSingleCell['row'];
							$table->rows[$key]['cells'][$index]['col'] = $this->isSingleCell['col'] - 1;

							// Because we can not calculate value after removing all unneeded cells
							if(!empty($table->rows[$key]['cells'][$index]['calculatedValue'])) {
								$table->rows[$key]['cells'][$index]['data'] = $table->rows[$key]['cells'][$index]['calculatedValue'];
							}
						} else {
							unset($table->rows[$key]['cells'][$index]);
						}
					}
				} else {
					unset($table->rows[$key]);
				}
			}
		}
		foreach($table->rows as $key => $row) {
			if(isset($row['cells']) && !empty($row['cells'])) {
				foreach($row['cells'] as $index => $cell) {
					if($this->isFromHistory) {
						if(!empty($this->isFromHistory[$key]['cells'][$index]) && in_array('data', array_keys($this->isFromHistory[$key]['cells'][$index]))) {
							$table->rows[$key]['cells'][$index]['data'] = $this->isFromHistory[$key]['cells'][$index]['data'];
						}
						if(!empty($this->isFromHistory[$key]['cells'][$index]) && in_array('calculatedValue', array_keys($this->isFromHistory[$key]['cells'][$index]))) {
							$table->rows[$key]['cells'][$index]['calculatedValue'] = $this->isFromHistory[$key]['cells'][$index]['calculatedValue'];
						}
					}
					$table->rows[$key]['cells'][$index]['data'] = do_shortcode($table->rows[$key]['cells'][$index]['data']);
				}
			}
		}
		$table->history = (bool) $this->isFromHistory;
		$table->encoded_title = htmlspecialchars($table->title, ENT_QUOTES);
        $renderData = $twig->render($this->getShortcodeTemplate(), array('table' => $table));
        $renderData = preg_replace('/\s+/', ' ', trim($renderData));
	    
        if (!$this->isSingleCell && isset($this->cacheDirectory)) {
            file_put_contents($cachePath, $renderData);
        }

		return $renderData;
    }

	/**
	 * Renders the value of single cell
	 * @param int $tableId
	 * @param int $tableRowId
	 * @param int $tableColId
	 * @return string
	 */
	public function renderCellValue($tableId, $tableRowId, $tableColId)
	{
		$value = $this->translate('No value');
		$environment = $this->getEnvironment();
		$core = $environment->getModule('core');
		$tables = $core->getModelsFactory()->get('tables');

		try {
			$rows = $tables->getRows($tableId);
		} catch (Exception $e) {
			return $this->ajaxError(
				sprintf($this->translate('Failed to get table rows: %s'), $e->getMessage())
			);
		}

		if(!empty($rows)) {
			foreach($rows as $key => $row) {
				if($key == $tableRowId - 1) {
					if(isset($row['cells']) && !empty($row['cells'])) {
						foreach($row['cells'] as $index => $cell) {
							if($index == $tableColId - 1) {
								if(!empty($rows[$key]['cells'][$index]['calculatedValue'])) {
									$value = $rows[$key]['cells'][$index]['calculatedValue'];
								} else {
									$value = $rows[$key]['cells'][$index]['data'];
								}
							}
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Renders the table with single cell
	 * @param int $tableId
	 * @param int $tableRowId
	 * @param int $tableColId
	 * @return string
	 */
	public function renderCellSingle($tableId, $tableRowId, $tableColId)
	{
		$this->isSingleCell = array('row' => $tableRowId, 'col' => $tableColId);

		return $this->render((int) $tableId);
	}

	/**
	 * Returns shortcode template name.
	 * @return string
	 */
	public function getShortcodeTemplate()
	{
		return '@tables/shortcode.twig';
	}

    public function doShortcode($attributes)
    {
        $environment = $this->getEnvironment();
        $config = $environment->getConfig();

        if (!array_key_exists('id', $attributes)) {
            return sprintf(
				$environment->translate('Mandatory attribute "id" is not specified. ' . 'Shortcode usage example: [%s id="{table_id}"]'),
				$config->get('shortcode_name')
			);
        }
        $ui = $environment->getModule('ui');										/** @var SupsysticTables_Ui_Module $ui */
        $assets = array_filter($ui->getAssets(), array($this, 'filterAssets'));		/** @var SupsysticTables_Ui_Asset[] $assets */

        if (count($assets) > 0) {
            foreach ($assets as $asset) {
                add_action('wp_footer', array($asset, 'load'));
            }
        }

        return $this->render((int)$attributes['id']);
    }

	private function registerValueShortcode() {
		$config = $this->getEnvironment()->getConfig();

		add_shortcode($config->get('shortcode_value_name'), array($this, 'doValueShortcode'));
	}

	public function doValueShortcode($attributes)
	{
		$environment = $this->getEnvironment();
		$config = $environment->getConfig();
		$shortcode = $config->get('shortcode_value_name');

		if (!array_key_exists('id', $attributes)
			|| !array_key_exists('row', $attributes)
			|| !array_key_exists('col', $attributes)
		) {
			return $environment->translate('There are not all shortcode\'s attributes specified. Usage example') . ':<br />'
			. sprintf('[%s id="{table id}" row="{row number}" col="{column number}"]', $shortcode);
		}

		return $this->renderCellValue((int)$attributes['id'], (int)$attributes['row'], (int)$attributes['col']);
	}

	private function registerCellShortcode() {
		$config = $this->getEnvironment()->getConfig();

		add_shortcode($config->get('shortcode_cell_name'), array($this, 'doCellShortcode'));
	}

	public function doCellShortcode($attributes)
	{
		$environment = $this->getEnvironment();
		$config = $environment->getConfig();
		$shortcode = $config->get('shortcode_cell_name');

		if (!array_key_exists('id', $attributes)
			|| !array_key_exists('row', $attributes)
			|| !array_key_exists('col', $attributes)
		) {
			return $environment->translate('There are not all shortcode\'s attributes specified. Usage example') . ':<br />'
			. sprintf('[%s id="{table id}" row="{row number}" col="{column number}"]', $shortcode);
		}

		/** @var SupsysticTables_Ui_Module $ui */
		$ui = $environment->getModule('ui');
		/** @var SupsysticTables_Ui_Asset[] $assets */
		$assets = array_filter($ui->getAssets(), array($this, 'filterAssets'));

		if (count($assets) > 0) {
			foreach ($assets as $asset) {
				add_action('wp_footer', array($asset, 'load'));
			}
		}

		return $this->renderCellSingle((int)$attributes['id'], (int)$attributes['row'], (int)$attributes['col']);
	}

	private function registerShortcode()
	{
		$config = $this->getEnvironment()->getConfig();
		$callable = array($this, 'doShortcode');

		add_shortcode(
			$config->get('shortcode_name'),
			$callable
		);
	}

	public function registerWidget() {
		register_widget('SupsysticTables_Widget');
	}

    private function registerTwigTableRender()
    {
        $twig = $this->getEnvironment()->getTwig();
        $callable = array($this, 'render');


        $twig->addFunction(new Twig_SimpleFunction('render_table', $callable, array('is_safe' => array('html'))));
    }

	private function addTwigHighlighter()
	{
		$twig = $this->getEnvironment()->getTwig();

		$twig->addFilter( new Twig_SimpleFilter('highlight', 'highlight_string', array('is_safe' => array('html'))));
	}

	/**
	 * Returns only not loaded assets
	 * @param \SupsysticTables_Ui_Asset $asset
	 * @return bool
	 */
	public function filterAssets(SupsysticTables_Ui_Asset $asset)
	{
		return !$asset->isLoaded() && 'wp_enqueue_scripts' === $asset->getHookName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function afterUiLoaded(SupsysticTables_Ui_Module $ui)
	{
		parent::afterUiLoaded($ui);

		$environment = $this->getEnvironment();
		$hookName = 'admin_enqueue_scripts';
		$dynamicHookName = is_admin() ? $hookName : 'wp_enqueue_scripts';

		$version = $environment->getConfig()->get('plugin_version');
		$cachingAllowed = $environment->isProd();

		$ui->add($ui->createScript('jquery')->setHookName($dynamicHookName));

		// Styles
		$ui->add(
			$ui->createStyle('supsystic-tables-datatables-css')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css')
				->setVersion('1.10.12')
				->setCachingAllowed(true)
		);

		$ui->add(
			$ui->createStyle('supsystic-tables-shortcode-css')
				->setHookName($dynamicHookName)
				->setModuleSource($this, 'css/tables.shortcode.css')
				->setVersion($version)
				->setCachingAllowed($cachingAllowed)
		);

		$ui->add(
			$ui->createStyle('supsystic-tables-datatables-responsive-css')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css')
				->setVersion('2.0.2')
				->setCachingAllowed(true)
		);

		$ui->add(
			$ui->createStyle('supsystic-tables-datatables-fixed-columns-css')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/fixedcolumns/3.2.2/css/fixedColumns.dataTables.min.css')
				->setVersion('3.2.2')
				->setCachingAllowed(true)
		);

		$ui->add(
			$ui->createStyle('supsystic-tables-datatables-fixed-headers-css')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/fixedheader/3.1.2/css/fixedHeader.dataTables.min.css')
				->setVersion('3.1.2')
				->setCachingAllowed(true)
		);

		// Scripts
		$ui->add(
			$ui->createScript('supsystic-tables-datatables-js')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js')
				->setVersion('1.10.12')
				->setCachingAllowed(true)
				->addDependency('jquery')
		);

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-responsive-js')
				->setHookName('wp_enqueue_scripts')
				->setExternalSource('//cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js')
				->setVersion('2.0.2')
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-fixed-columns-js')
				->setHookName('wp_enqueue_scripts')
				->setExternalSource('//cdn.datatables.net/fixedcolumns/3.2.2/js/dataTables.fixedColumns.min.js')
				->setVersion('3.2.2')
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-fixed-headers-js')
				->setHookName('wp_enqueue_scripts')
				->setExternalSource('//cdn.datatables.net/fixedheader/3.1.2/js/dataTables.fixedHeader.min.js')
				->setVersion('3.2.2')
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-numeral')
				->setHookName($dynamicHookName)
				->setModuleSource($this, 'libraries/numeral.min.js')
				->setVersion($version)
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-natural-sort-js')
				->setHookName($dynamicHookName)
				->setExternalSource('//cdn.datatables.net/plug-ins/1.10.11/sorting/natural.js')
				->setVersion('1.10.11')
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

		/* RuleJS */
		$this->loadRuleJS($ui);

		/* Backend scripts */
		if ($environment->isModule('tables')) {
			$ui->add(
				$ui->createScript('jquery-ui-dialog')
			);

			$ui->add(
				$ui->createScript('jquery-ui-autocomplete')
			);

			$ui->add(
				$ui->createScript('supsystic-tables-tables-model')
					->setHookName($hookName)
					->setModuleSource($this, 'js/tables.model.js')
					->setCachingAllowed($cachingAllowed)
					->setVersion($version)
			);

			if ($environment->isAction('index')) {
				$ui->add(
					$ui->createScript('supsystic-tables-tables-index')
						->setHookName($hookName)
						->setModuleSource($this, 'js/tables.index.js')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
						->addDependency('jquery-ui-dialog')
				);
			}

			if ($environment->isAction('view')) {

				// DataTables language selector
				// $ui->add(
				//     $ui->createScript('supsystic-tables-dt-lang-selector')
				//         ->setHookName($hookName)
				//         ->setModuleSource($this, 'js/dt.lang-selector.js')
				// );

				$ui->add(
					$ui->createStyle('supsystic-tables-tables-editor-css')
						->setHookName($hookName)
						->setModuleSource($this, 'css/tables.editor.css')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
				);

				/* Color Picker */
				$ui->add(
					$ui->createStyle('supsystic-tables-colorpicker-css')
						->setHookName($hookName)
						->setModuleSource($this, 'libraries/colorpicker/colorpicker.css')
						->setCachingAllowed(true)
				);

				$ui->add(
					$ui->createScript('supsystic-tables-colorpicker-js')
						->setHookName($hookName)
						->setModuleSource($this, 'libraries/colorpicker/colorpicker.js')
						->setCachingAllowed(true)
				);

				/* Toolbar */
				$ui->add(
					$ui->createStyle('supsystic-tables-toolbar-css')
						->setHookName($hookName)
						->setModuleSource($this, 'libraries/toolbar/jquery.toolbars.css')
						->setCachingAllowed(true)
				);

				$ui->add(
					$ui->createScript('supsystic-tables-toolbar-js')
						->setHookName($hookName)
						->setModuleSource($this, 'libraries/toolbar/jquery.toolbar.js')
						->setCachingAllowed(true)
				);

				/* Handsontable */
				$ui->add(
					$ui->createStyle('supsystic-tables-handsontable-css')
						->setHookName($hookName)
						->setExternalSource('https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.31.0/handsontable.full.min.css')
						->setCachingAllowed(true)
						->setVersion('0.31.0')
				);

				$ui->add(
					$ui->createScript('supsystic-tables-handsontable-js')
						->setHookName($hookName)
						->setExternalSource('https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.31.0/handsontable.full.min.js')
						->setCachingAllowed(true)
						->setVersion('0.31.0')
				);

				$ui->add(
					$ui->createScript('supsystic-tables-editor-toolbar-js')
						->setHookName($hookName)
						->setModuleSource($this, 'js/editor/tables.editor.toolbar.js')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
				);

				$ui->add(
					$ui->createScript('supsystic-tables-editor-formula-js')
						->setHookName($hookName)
						->setModuleSource($this, 'js/editor/tables.editor.formula.js')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
						->addDependency('jquery-ui-autocomplete')
				);

				$ui->add(
					$ui->createStyle('supsystic-tables-tables-view')
						->setHookName($hookName)
						->setModuleSource($this, 'css/tables.view.css')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
				);

				$ui->add(
					$ui->createScript('supsystic-tables-tables-view')
						->setHookName($hookName)
						->setModuleSource($this, 'js/tables.view.js')
						->setCachingAllowed($cachingAllowed)
						->setVersion($version)
				);

				$ui->add(
					$ui->createScript('supsystic-tables-ace-editor-js')
						->setHookName($hookName)
						->setModuleSource($this, 'js/ace/ace.js')
				);

				$ui->add(
					$ui->createScript('supsystic-tables-ace-editor-mode-js')
						->setHookName($hookName)
						->setModuleSource($this, 'js/ace/mode-css.js')
						->addDependency('supsystic-tables-ace-editor-js')
				);

				$ui->add(
					$ui->createScript('supsystic-tables-ace-editor-theme-js')
						->setHookName($hookName)
						->setModuleSource($this, 'js/ace/theme-monokai.js')
						->addDependency('supsystic-tables-ace-editor-js')
				);
			}
		}

		$ui->add(
			$ui->createScript('supsystic-tables-shortcode')
				->setHookName($dynamicHookName)
				->setModuleSource($this, 'js/tables.shortcode.js')
				->setVersion($version)
				->setCachingAllowed($cachingAllowed)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
				->addDependency('supsystic-tables-datatables-numeral')
		);
	}

    private function loadRuleJS(SupsysticTables_Ui_Module $ui)
    {
        $hookName = 'admin_enqueue_scripts';
        $dynamicHookName = is_admin() ? $hookName : 'wp_enqueue_scripts';

        if (is_admin() && !$this->getEnvironment()->isModule('tables', 'view')) {
            return;
        }

        /* External Libraries */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-libs-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/ruleJS.lib.full.js')
        );

        /* RuleJS */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-parser-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/parser.js')
        );

        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/ruleJS.js')
        );

        /* Handsontable Module */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-hot-js')
                ->setHookName($hookName)
                ->setModuleSource($this, 'libraries/ruleJS/handsontable.formula.js')
                ->addDependency('supsystic-tables-handsontable-js')
        );

        $ui->add(
            $ui->createStyle('supsystic-tables-rulejs-hot-css')
                ->setHookName($hookName)
                ->setModuleSource($this, 'libraries/ruleJS/handsontable.formula.css')
        );
    }

    private function registerMenuItem()
    {
        $environment = $this->getEnvironment();
        $menu = $environment->getMenu();
        $plugin_menu = $this->getConfig()->get('plugin_menu');
        $capability = $plugin_menu['capability'];

        $item = $menu->createSubmenuItem();
        $item->setCapability($capability)
            ->setMenuSlug($menu->getMenuSlug() . '#add')
            ->setMenuTitle($environment->translate('Add table'))
            ->setModuleName('tables')
            ->setPageTitle($environment->translate('Add table'));
		// Avoid conflicts with old vendor version
		if(method_exists($item, 'setSortOrder')) {
			$item->setSortOrder(20);
		}

        $menu->addSubmenuItem('add_table', $item);

        $item = $menu->createSubmenuItem();
        $item->setCapability($capability)
           ->setMenuSlug($menu->getMenuSlug() . '&module=tables')
           ->setMenuTitle($environment->translate('Tables'))
           ->setModuleName('tables')
           ->setPageTitle($environment->translate('Tables'));
		// Avoid conflicts with old vendor version
		if(method_exists($item, 'setSortOrder')) {
			$item->setSortOrder(30);
		}

        $menu->addSubmenuItem('tables', $item);
		
		// We change Settings submenu position
		if($menu->getSubmenuItem('settings')) {
			$settings = $menu->getSubmenuItem('settings');
			$menu->deleteSubmenuItem('settings');
			$menu->addSubmenuItem('settings', $settings);
		}
    }

    public function disallowIndexing($id) {

        $core = $this->getEnvironment()->getModule('core');
        $tables = $core->getModelsFactory()->get('tables');
        $settings = $tables->getSettings($id);

        if (!$settings) {
            return false;
        }

        $settings = unserialize(htmlspecialchars_decode(current($settings)->settings));
        if (!isset($settings['disallowIndexing'])) {
            return false;
        }

        $userAgent = $this->getRequest()->headers->get('USER_AGENT');
        $pattern = '/(abachobot|acoon|aesop_com_spiderman|ah-ha.com crawler|appie|arachnoidea|architextspider|atomz|baidu|bing|bot|deepindex|esismartspider|ezresult|fast-webcrawler|feed|fido|fluffy the spider|gigabot|google|googlebot|gulliver|gulper|gulper|henrythemiragorobot|http|ia_archiver|jeevesteoma|kit-fireball|linkwalker|lnspiderguy|lycos_spider|mantraagent|mediapartners|msn|nationaldirectory-superspider|nazilla|openbot|openfind piranha,shark|robozilla|scooter|scrubby|search|slurp|sogou|sohu|soso|spider|tarantula|teoma_agent1|test|uk searcher spider|validator|w3c_validator|wdg_validator|webaltbot|webcrawler|websitepulse|wget|winona|yahoo|yodao|zyborg)/i';
        return (bool) preg_match($pattern, $userAgent);
    }

    public function reviewNoticeCheck() {
        $option = $this->config('db_prefix') . 'reviewNotice';
        $notice = get_option($option);
        if (!$notice) {
            update_option($option, array(
                'time' => time() + (60 * 60 * 24 * 7),
                'shown' => false
            ));
        } elseif ($notice['shown'] === false && time() > $notice['time']) {
            add_action('admin_notices', array($this, 'showReviewNotice'));
        }
    }

    public function showReviewNotice() {
        print $this->getTwig()->render('@tables/notice/review.twig');
    }

	public function setIniLimits() {
		// Override local and wp limits
		if(strlen(ini_get('memory_limit')) < 4) {
			ini_set('memory_limit', '12000M');
		}
		if(strlen(ini_get('connect_timeout')) < 2) {
			ini_set('connect_timeout', 24000);
		}
		if(strlen(ini_get('max_execution_time')) < 2) {
			ini_set('max_execution_time', 24000);
		}
		if(strlen(ini_get('max_input_time')) < 2) {
			ini_set('max_input_time', 24000);
		}
	}

	/**
	 * Executes after module loaded.
	 */
	public function onAfterLoaded()
	{
		$config = $this->getEnvironment()->getConfig();
	}

	/**
	 * Call wp footer manualy on broken themes to ensure than scripts are loaded
	 */
	public function onShutdown() {
		if (!is_admin() && did_action('after_setup_theme') && did_action('get_footer') && !did_action('wp_footer')) {
			wp_footer();
		}
	}

	/**
	 * Runs the callbacks after the table editor tabs rendered.
	 */
	private function renderTableHistorySection()
	{
		$dispatcher = $this->getEnvironment()->getDispatcher();

		$dispatcher->on('tabs_rendered', array($this, 'afterTabsRendered'));
		$dispatcher->on('tabs_content_rendered', array($this, 'afterTabsContentRendered'));
	}

	/**
	 * Renders the "TableHistory" tab.
	 * @param \stdClass $table Current table
	 */
	public function afterTabsRendered()
	{
		$twig = $this->getEnvironment()->getTwig();
		$twig->display('@tables/partials/historyTab.twig', array());
	}

	/**
	 * Renders the "TableHistory" tab content.
	 * @param \stdClass $table Current table
	 */
	public function afterTabsContentRendered($table)
	{
		$twig = $this->getEnvironment()->getTwig();
		$dispatcher = $this->getEnvironment()->getDispatcher();

		$twig->display(
			$dispatcher->apply('table_history_tabs_content_template', array('@tables/partials/historyTabContent.twig')),
			$dispatcher->apply('table_history_tabs_content_data', array(array( 'table' => $table )))
		);
	}
}

require_once('Model/widget.php');