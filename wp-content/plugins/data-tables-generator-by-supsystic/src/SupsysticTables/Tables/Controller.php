<?php


class SupsysticTables_Tables_Controller extends SupsysticTables_Core_BaseController
{
    /**
     * Shows the list of the tables.
     * @return Rsc_Http_Response
     */
    public function indexAction()
    {
        try {
			$this->getEnvironment()->getModule('tables')->setIniLimits();
            $tables = $this->getModel('tables')->getAll(
				array(
					'order'    => 'DESC',
					'order_by' => 'id'
				)
			);
            return $this->response('@tables/index.twig', array('tables' => $tables));
        } catch (Exception $e) {
            return $this->response('error.twig', array('exception' => $e));
        }
    }

    /**
     * Validate and creates the new table.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function createAction(Rsc_Http_Request $request)
    {
        $title = trim($request->post->get('title'));
        $rowsCount = (int) $request->post->get('rows');
        $colsCount = (int) $request->post->get('cols');

        try {
			if (!$this->isValidTitle($title)) {
                return $this->ajaxError(
                    $this->translate(
                        'Title can\'t be empty or more than 255 characters'
                    )
                );
            }
			$this->getEnvironment()->getModule('tables')->setIniLimits();
			// Add base settings
            $tableId = $this->getModel('tables')->add(array('title' => $title, 'settings' => serialize(array())));

			if($tableId) {
				$rows = array();

				for($i = 0; $i < $rowsCount; $i++) {
					array_push($rows, array('cells' => array()));
					for($j = 0; $j < $colsCount; $j++) {
						array_push($rows[$i]['cells'], array(
							'data' => '',
							'calculatedValue' => '',
                   			'hidden' => '',
                    		'type' => 'text',
                    		'formatType' => '',
							'meta' => array()
						));
					}
				}
				// Save an empty table's rows to prevent error when the Data Tables script will be executed
				$this->getModel('tables')->setRows($tableId, $rows);
			}
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccess(
            array(
                'url' => $this->generateUrl(
                    'tables',
                    'view',
                    array('id' => $tableId)
                )
            )
        );
    }

    /**
     * Removes the table.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function removeAction(Rsc_Http_Request $request)
    {
        $id = $this->isAjax() ? $request->post->get('id') : $request->query->get('id');

        try {
            $this->getModel('tables')->removeById($id);
        } catch (Exception $e) {
            if ($this->isAjax()) {
                return $this->ajaxError($e->getMessage());
            }

            return $this->response('error.twig', array('exception' => $e));
        }

        if ($this->isAjax()) {
            return $this->ajaxSuccess();
        }

        return $this->redirect($this->generateUrl('tables'));
    }

    /**
     * Show the table settings, editor, etc.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function viewAction(Rsc_Http_Request $request)
    {
		$this->getEnvironment()->getModule('tables')->setIniLimits();

		try {
            wp_enqueue_media();
            $id = $request->query->get('id');
			$table = $this->getModel('tables')->getById($id);
        } catch (Exception $e) {
            return $this->response('error.twig', array('exception' => $e));
        }
		if(isset($table->settings['features']['after_table_loaded_script']) && !empty($table->settings['features']['after_table_loaded_script'])) {
			$table->settings['features']['after_table_loaded_script'] = base64_decode($table->settings['features']['after_table_loaded_script']);
		}
        /** @var SupsysticTables_Tables_Model_Languages $languages */
        $languages = $this->getModel('languages', 'tables');
        $config = $this->getEnvironment()->getConfig();

        return $this->response(
            '@tables/view.twig',
            array(
                'table'             => $table,
                'attributes'        => array(
                    'cols' => $request->query->get('cols', 5),
                    'rows' => $request->query->get('rows', 5)
                ),
				'shortcode_name' => $config->get('shortcode_name'),
				'shortcode_value_name' => $config->get('shortcode_value_name'),
				'shortcode_cell_name' => $config->get('shortcode_cell_name'),
                'translations'      => $languages->getLanguages(),
            )
        );
    }

    /**
     * Renames the table.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function renameAction(Rsc_Http_Request $request)
    {
        $id = $request->post->get('id');
        $title = trim($request->post->get('title'));

        try {
            $this->getModel('tables')->set($id, array(
                'title' => $title
            ));
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccess();
    }

    /**
     * Returns the table columns.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function getColumnsAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
        $id = $request->post->get('id');

        try {
            return $this->ajaxSuccess(
                array('columns' => $tables->getColumns($id))
            );
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * Updates the table columns.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function updateColumnsAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
        $id = $request->post->get('id');
        $columns = $request->post->get('columns');

        try {
            $tables->setColumns($id, $columns);
        } catch (Exception $e) {
            return $this->ajaxError(
                sprintf(
                    $this->translate(
                        'Failed to save table columns: %s'
                    ),
                    $e->getMessage()
                )
            );
        }

        return $this->ajaxSuccess();
    }

    /**
     * Returns the table rows.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function getRowsAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
        $id = $request->post->get('id');

        try {
			$this->getEnvironment()->getModule('tables')->setIniLimits();

            return $this->ajaxSuccess(array(
                'rows' => $tables->getRows($id)
            ));
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * Updates the table rows.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function updateRowsAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
        $id = $request->post->get('id');
        $step = $request->post->get('step');
		$last = $request->post->get('last');
		$rowsData = $request->post->get('rows');
		$rows = $this->prepareData($rowsData);

        // ticket #1024
        if (null === $rows) {
            $message = $this->translate('Can\'t decode table rows from JSON.');

            if (function_exists('json_last_error')) {
                $message .= 'Error: ' . json_last_error();
            }

            return $this->ajaxError($message);
        }

        try {
			$this->getEnvironment()->getModule('tables')->setIniLimits();

			if(!empty($step)) {
				$tables->setRowsByPart($id, $rows, $step, $last);
			} else {
				$tables->setRows($id, $rows);
			}

        } catch (Exception $e) {
            return $this->ajaxError(
                sprintf(
                    $this->translate(
                        'Failed to save table rows: %s'
                    ),
                    $e->getMessage()
                )
            );
        }

        return $this->ajaxSuccess();
    }

    /**
     * Saves the table settings.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function saveSettingsAction(Rsc_Http_Request $request)
    {
        $id = $request->post->get('id');
        $data = $request->post->get('settings');
        if (get_magic_quotes_gpc()) {
           $data = stripslashes($data);
        }
        parse_str($data, $settings);

        try {
			$this->getEnvironment()->getModule('tables')->setIniLimits();
            $this->getModel('tables')->set($id, array('settings' => serialize($settings)));
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }

        $this->cleanCache($id);
        return $this->ajaxSuccess();
    }

    /**
     * Renders the table.
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function renderAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Module $tables */
        $tables = $this->getEnvironment()->getModule('tables');
        $id = $request->post->get('id');
		$tables->setIniLimits();

        return $this->ajaxSuccess(array('table' => $tables->render((int)$id)));
    }

    /**
     * Updates table meta (Cells merging, etc)
     * @param \Rsc_Http_Request $request
     * @return \Rsc_Http_Response
     */
    public function updateMetaAction(Rsc_Http_Request $request)
    {
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
        $id = $request->post->get('id');
        $metaData = $request->post->get('meta');
        $meta = $this->prepareData($metaData);

        // ticket #1024
        if (null === $meta) {
            $message = $this->translate('Can\'t decode table meta from JSON.');

            if (function_exists('json_last_error')) {
                $message .= 'Error: ' . json_last_error();
            }

            return $this->ajaxError($message);
        }

        try {
			$this->getEnvironment()->getModule('tables')->setIniLimits();
            $tables->setMeta($id, $meta);
        } catch (Exception $e) {
            return $this->ajaxError(
                sprintf(
                    $this->translate('Failed to save table meta data: %s'),
                    $e->getMessage()
                )
            );
        }

        return $this->ajaxSuccess();
    }

    public function getMetaAction(Rsc_Http_Request $request)
    {
        $id = $request->post->get('id');
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $this->getModel('tables');
		$this->getEnvironment()->getModule('tables')->setIniLimits();
        $table = $tables->getById($id);

        return $this->ajaxSuccess(array('meta' => $table->meta));
    }

    /**
     * Validates the table title.
     * @param string $title
     * @return bool
     */
    protected function isValidTitle($title)
    {
        return is_string($title) && ($title !== '' && strlen($title) < 255);
    }

	public function sendUsageStat($state) {
		$apiUrl = 'http://updates.supsystic.com';

		$reqUrl = $apiUrl . '?mod=options&action=saveUsageStat&pl=rcs';
		wp_remote_post($reqUrl, array(
			'body' => array(
				'site_url' => get_bloginfo('wpurl'),
				'site_name' => get_bloginfo('name'),
				'plugin_code' => 'stb',
				'all_stat' => array('views' => 'review', 'code' => $state),
			)
		));

		return true;
	}

    public function cleanCache($id)
    {
        $cachePath = $this->getConfig()->get('plugin_cache_tables') . 
        DIRECTORY_SEPARATOR . $id;
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

	public function prepareData($data) {
		$decodedData = '';

		if(is_array($data)) {
			foreach ($data as $d) {
				$decodedData .= $d;
			}
		} else {
			$decodedData = $data;
		}
		if (get_magic_quotes_gpc()) {
			$decodedData = stripslashes($decodedData);
		}
		$decodedData = json_decode($decodedData, true);

		return $decodedData;
	}

    public function cloneTableAction(Rsc_Http_Request $request)
    {
		$this->getEnvironment()->getModule('tables')->setIniLimits();
		$id = $request->post->get('id');
        $title = $request->post->get('title');
        $tableModel = $this->getModel('tables');
        $clonedTable = $tableModel->getById($id);

        try {
            if (!$this->isValidTitle($title)) {
                return $this->ajaxError(
                    $this->translate(
                        'Title can\'t be empty or more than 255 characters'
                    )
                );
            }

            $tableId = $tableModel->add(
                array(
                    'title' => $title, 
                    'settings' => serialize($clonedTable->settings),
                    'meta' => serialize($clonedTable->meta)
                )
            );

			$newTableMeta = $clonedTable->meta;
			$newTableMeta['css'] = preg_replace('/#supsystic-table-(\d+)/', '#supsystic-table-' . $tableId, $clonedTable->meta['css']);
			$tableModel->setMeta($tableId,$newTableMeta);
            $tableModel->setRows($tableId, $clonedTable->rows);

            return $this->ajaxSuccess(array('id' => $tableId));

        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function reviewNoticeResponseAction(Rsc_Http_Request $request) {
        $responseCode = $request->post->get('responseCode');
        $option = $this->getConfig()->get('db_prefix') . 'reviewNotice';

        if ($responseCode === 'later') {
            update_option($option, array(
                'time' => time() + (60 * 60 * 24 * 2),
                'shown' => false
            ));
        } else {
            update_option($option, array(
                'shown' => true
            ));
        }

        return $this->ajaxSuccess();
    }
}
