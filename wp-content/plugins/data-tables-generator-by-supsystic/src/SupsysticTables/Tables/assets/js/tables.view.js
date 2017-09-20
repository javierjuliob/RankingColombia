(function ($, app, undefined) {
	$(document).ready(function () {
		// If turn on chosen plugin for selects of all types - there is conflict with handsontable plugin happen
		$('#row-tab-settings select[multiple="multiple"]').chosen({width: '100%'});

		var tableId = app.getParameterByName('id'),
			windowHeight = $(window).height(),
			ace = window.ace.edit("css-editor"),
			editor, toolbar, formula;

		// Make editors responsive for window height (810px is mobile responsive width)
		if($(window).width() > 810 && windowHeight > 650) {
			windowHeight = windowHeight - 350;
		}

		$('#tableEditor, #css-editor').css({
			'max-height': windowHeight,
			'min-height': windowHeight,
			'height': windowHeight
		});

		// Main Buttons Actions
		var $cloneDialog = $('#cloneDialog').dialog({
			autoOpen: false,
			width:    480,
			modal:    true,
			open: function() {
				$(this).next().find('button:first-of-type').html('Clone').show();
			},
			buttons:  {
				Clone: function (event) {
					var $dialog = $(this),
						$button = $(event.target).closest('button');

					$button.attr('disabled', true);
					$button.html(app.createSpinner());
					app.Models.Tables.request('cloneTable', {
						id: app.getParameterByName('id'),
						title: $(this).find('input').val()
					}).done(function(response) {
						if (response.success) {
							var html = '<a href="' + app.replaceParameterByName(window.location.href, 'id', response.id) + '" class="ui-button" style="padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none !important;">Open cloned table</a><div style="float: right; margin-top: 5px;">Done!</div>';

							$button.hide();
							$dialog.find('.input-group').hide();
							$dialog.find('.input-group').after($('<div>', {class: 'message', html: html}));
						}
					});
				},
				Cancel: function () {
					$(this).dialog('close');
				}
			}
		});

		$('#buttonClone').on('click', function () {
			$cloneDialog.dialog('open');
			$cloneDialog.find('.message').remove();
			$cloneDialog.find('.input-group').show();
			$cloneDialog.next().find('button:eq(1)').removeAttr('disabled');
			$cloneDialog.find('input').val($.trim($('.table-title').text()) + '_Clone');
		});

		$('#buttonSave').on('click', function () {
			saveTable();
		});

		$('#buttonDelete').on('click', function () {
			var $button = $(this);

			if (!confirm('Are you sure you want to delete the this table?')) {
				return;
			}

			app.createSpinner($button);
			app.Models.Tables.remove(app.getParameterByName('id'))
				.done(function () {
					window.location.href = $('#menuItem_tables').attr('href');
				})
				.fail(function (error) {
					alert('Failed to delete table: ' + error);
				})
				.always(function () {
					app.deleteSpinner($button);
				});
		});

		$('#buttonClearData').on('click', function () {
			if (!confirm('Are you sure you want to clear all data in this table?')) {
				return;
			}
			editor.clear();
		});

		// Custom Hansontabe Renderers
		Handsontable.renderers.CustomHtmlRenderer = function (instance, td, row, col, prop, value, cellProperties) {
			Handsontable.renderers.HtmlRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
			if (td.innerHTML === 'null') {
				td.innerHTML = '';
			}
		};

		Handsontable.renderers.DefaultRenderer = function (instance, td, row, col, prop, value, cellProperties) {
			var cellMeta = instance.getCellMeta(row,  row);

			if(app.Models.Tables.isFormula(value)) {
				Handsontable.TextCell.renderer.apply(this, arguments);
				value = app.Models.Tables.getFormulaResult(value, row, col);
			}

			if(instance.useNumberFormat && (app.Models.Tables.isNumber(value) || cellMeta.formatType == 'number')) {
				Handsontable.renderers.NumberRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
			} else {
				Handsontable.renderers.CustomHtmlRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
			}
		};

		Handsontable.renderers.NumberRenderer = function (instance, td, row, col, prop, value, cellProperties) {
			value = app.Models.Tables.setCellFormat(value, 'number');

			Handsontable.renderers.CustomHtmlRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
		};

		Handsontable.renderers.CurrencyRenderer = function (instance, td, row, col, prop, value, cellProperties) {
			if(value) {
				if(app.Models.Tables.isFormula(value)) {
					Handsontable.TextCell.renderer.apply(this, arguments);
					value = app.Models.Tables.getFormulaResult(value, row, col);
				}

				value = app.Models.Tables.setCellFormat(value, 'currency');
			}

			Handsontable.renderers.CustomHtmlRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
		};

		Handsontable.renderers.PercentRenderer = function (instance, td, row, col, prop, value, cellProperties) {
			if(value) {
				if(app.Models.Tables.isFormula(value)) {
					Handsontable.TextCell.renderer.apply(this, arguments);
					value = app.Models.Tables.getFormulaResult(value, row, col);
				}

				value = app.Models.Tables.setCellFormat(value, 'percent');
			}

			Handsontable.renderers.CustomHtmlRenderer.call(this, instance, td, row, col, prop, value, cellProperties);
		};

		Handsontable.editors.TextEditor.prototype.beginEditing = function () {
			// To show percents as is if it is pure number
			var formatType = this.cellProperties.formatType || '',
				value = this.originalValue;

			if(app.Models.Tables.isNumber(value) && !app.Models.Tables.isFormula(value)) {
				if(formatType == 'percent') {
					value = (value * 100).toString();
				}
			}

			this.originalValue = value;

			Handsontable.editors.BaseEditor.prototype.beginEditing.call(this);
		};

		Handsontable.editors.TextEditor.prototype.focus = function() {
			this.TEXTAREA.select();
		};

		Handsontable.editors.TextEditor.prototype.saveValue = function (val, ctrlDown) {
			// Correct save of percent values
			var formatType = this.cellProperties.formatType || '',
				value = val[0][0];

			if(app.Models.Tables.isNumber(value) && !app.Models.Tables.isFormula(value)) {
				if (formatType == 'percent') {
					value = (value / 100).toString();
				}
			}

			val[0][0] = value;

			Handsontable.editors.BaseEditor.prototype.saveValue.call(this, val, ctrlDown);
		};

		ace.setTheme("ace/theme/monokai");
		ace.getSession().setMode("ace/mode/css");

		initializeTabs() ;
		initializeSettingsTabs() ;

		editor = initializeEditor();

		// Editor Hooks
		editor.addHook('beforeChange', function (changes, source) {
			$.each(changes, function (index, changeSet) {
				var row = changeSet[0],
					col = changeSet[1],
					value = changeSet[3],
					cell = editor.getCellMeta(row, col);

				if (cell.type == 'date') {
					var newDate = moment(value, cell.format);

					if (newDate.isValid()) {
						changeSet[3] = newDate.format(cell.format);
					}
				}
			});

		});
		editor.addHook('afterChange', function (changes) {
			if (!$.isArray(changes) || !changes.length) {
				return;
			}

			$.each(changes, function (index, changeSet) {
				var row = changeSet[0],
					col = changeSet[1],
					value = changeSet[3];

				if (value.toString().match(/\\/)) {
					editor.setDataAtCell(row, col, value.replace(/\\/g, '&#92;'));
				}
			});

			editor.render();

		});
		var updateHeight = function(currentRow, newSize, isDoubleClick) {
			var heights = window.cellHeights;
			heights[currentRow] = newSize;

			editor.updateSettings({
				rowHeights: heights,
			});

			window.cellHeights = heights;
		}

		editor.addHook('afterRowResize', function(currentRow, newSize, isDoubleClick) {
			if(newSize > 22) {
				updateHeight(currentRow, newSize, isDoubleClick);
			} else {
				updateHeight(currentRow, 22, isDoubleClick);
			}
		});

		editor.addHook('afterLoadData', function () {
			generateWidthData();
		});
		editor.addHook('afterCreateRow', function(insertRowIndex, amount, source) {
			var data = editor.getData(),
				i = insertRowIndex - 1;

			if(source == 'ContextMenu.rowAbove') {
				i = insertRowIndex + 1;
			}
			setTimeout(function() {
				for(var j = 0; j < data[insertRowIndex].length; j++) {
					editor.setCellMetaObject(insertRowIndex, j, editor.getCellMeta(i, j));
				}
				editor.render();
			}, 10);

		});
		editor.addHook('afterCreateCol', function(insertColumnIndex, amount, source) {
			insertColumnIndex = typeof(insertColumnIndex) != 'undefined' ? insertColumnIndex : 0;

			var selectedCell = editor.getSelected()
			,	selectedColumnIndex = 0
			,	data = editor.getData()
			,	index = insertColumnIndex
			,	j = insertColumnIndex - 1;
			if(selectedCell && selectedCell[1] && selectedCell[1] > 0) {
				selectedColumnIndex = selectedCell[1];
			}

			if (insertColumnIndex > selectedColumnIndex) {
				index = insertColumnIndex - 1;
			}
			if(source == 'ContextMenu.columnRight') {
				j = insertColumnIndex + 1;
			}
			setTimeout(function() {
				for(var i = 0; i < data.length; i++) {
					editor.setCellMetaObject(i, insertColumnIndex, editor.getCellMeta(i, j))
				}
				editor.render();
			}, 10);
			generateWidthData();
			editor.allWidths.splice(selectedColumnIndex, 0, editor.allWidths[index]);
		});
		editor.addHook('afterRemoveCol', function(from, amount) {
			generateWidthData();
			editor.allWidths.splice(from, amount);

			var countCols = editor.countCols(),
				colWidth,
				allWidths = editor.allWidths,
				plugin = editor.getPlugin('ManualColumnResize');

			for (var i = 0; i < countCols; i++) {
				colWidth = editor.getColWidth(i);
				if (colWidth !== allWidths[i]) {
					plugin.setManualSize(i, allWidths[i]);
				}
			}
		});
		editor.addHook('afterColumnResize', function(column, width) {
			generateWidthData();
			editor.allWidths.splice(column, 1, width);
		});

		toolbar = new app.Editor.Toolbar('#tableToolbar', editor);
		formula = new app.Editor.Formula(editor);

		window.editor = editor;
		app.Editor.Hot = editor;
		app.Editor.Tb = toolbar;

		toolbar.subscribe();
		formula.subscribe();

		var loading = $.when(
			app.Models.Tables.getMeta(app.getParameterByName('id')),
			app.Models.Tables.getRows(tableId)
		);

		loading.done(function (metaResponse, rowsResponse) {
			var rows = rowsResponse[0].rows,
				meta = metaResponse[0].meta,
				comments = [];

			// Set merged cells
			if (typeof meta === 'object' && 'mergedCells' in meta && meta.mergedCells.length) {
				editor.updateSettings({
					mergeCells: meta.mergedCells
				});
			}

			// Set rows data
			if (rows.length > 0) {
				var data = [], cellMeta = [], heights = [], widths = [];

				// Colors
				var $style = $('#supsystic-tables-style');

				if (!$style.length) {
					$style = $('<style/>', { id: 'supsystic-tables-style' });
					$('head').append($style);
				}

				$.each(rows, function (x, row) {
					var cells = [];

					heights.push(row.height || undefined);

					$.each(row.cells, function (y, cell) {
						var metaData = {};

						cells.push(cell.data);

						if ('meta' in cell && cell.meta !== undefined) {
							var color = /color\-([0-9abcdef]{6})/.exec(cell.meta),
								background = /bg\-([0-9abcdef]{6})/.exec(cell.meta);

							if (null !== color) {
								$style.html($style.html() + ' .'+color[0]+' {color:#'+color[1]+' !important}');
							}

							if (null !== background) {
								$style.html($style.html() + ' .'+background[0]+' {background-color:#'+background[1]+' !important}');
							}

							metaData = $.extend(metaData, { row: x, col: y, className: cell.meta.join(' ') });
						}

						if (cell.formatType) {
							metaData = $.extend(metaData, {
								type: cell.type == 'numeric' ? 'text' : cell.type, // To remove numeric renderer
								format: cell.type == 'numeric' ? '' : cell.format,
								formatType: cell.type == 'numeric' ? '' : cell.formatType
							});
						} else {
							if(app.Models.Tables.isNumber(cell.data)) {
								metaData = $.extend(metaData, {
									type: 'text',
									format: '',
									formatType: 'number'
								});
							}
						}

						switch(cell.formatType) {
							case 'currency':
								metaData.renderer = Handsontable.renderers.CurrencyRenderer;
								break;
							case 'percent':
								metaData.renderer = Handsontable.renderers.PercentRenderer;
								break;
							case 'date':
								metaData.type = 'date';
								metaData.dateFormat = cell.format;
								metaData.correctFormat =  true;
								break;
							default:
								metaData.renderer = Handsontable.renderers.DefaultRenderer;
								break;
						}

						cellMeta.push(metaData);

						if (x === 0 && meta.columnsWidth) {
							widths.push(meta.columnsWidth[y] > 0 ? meta.columnsWidth[y] : 62);
						} else if (x === 0 ) {
							// Old
							widths.push(cell.width === undefined ? 62 : cell.width);
						}

						if (typeof(cell.comment) != 'undefined') {
							comments.push({
								col:     y,
								row:     x,
								comment: cell.comment
							});
						}

					});

					data.push(cells);
				});

				// Height & width
				window.cellHeights = heights;
				editor.updateSettings({
					rowHeights: heights,
					colWidths: widths
				});

				// Load extracted data
				editor.loadData(data);

				// Comments
				// Note: comments need to be loaded after editor.loadData() call.
				if (comments.length) {
					editor.updateSettings({
						cell: comments
					});
				}

				// Load extracted metadata
				$.each(cellMeta, function (i, meta) {
					editor.setCellMetaObject(meta.row, meta.col, meta);
				});
			}
		}).fail(function (error) {
			alert('Failed to load table data: ' + error);
		}).always(function (response) {
			$('#loadingProgress').remove();
			editor.render();
		});

		// Edit table title
		$('.table-title[contenteditable]').on('keydown', function (e) {
			if (!('keyCode' in e) || e.keyCode !== 13) {
				return;
			}

			var $heading = $(this),
				title = $heading.text();

			$heading.removeAttr('contenteditable');
			app.createSpinner($heading);
			app.Models.Tables.rename(app.getParameterByName('id'), title)
				.done(function () {
					$heading.text(title);
					$heading.attr('data-table-title', title);
				})
				.fail(function (error) {
					$heading.text($heading.attr('data-table-title'));
					alert('Failed to rename table: ' + error);
				});
		});

		var formSettings = $('form#settings'),
			head = formSettings.find('[name="elements[head]"]'),
			foot = formSettings.find('[name="elements[foot]"]'),
			fixedHead = formSettings.find('[name="fixedHeader"]'),
			fixedFoot = formSettings.find('[name="fixedFooter"]');

		// Set numbers
		formSettings.find('[name="useNumberFormat"]').on('change ifChanged', function() {
			if($(this).is(':checked')) {
				app.Editor.Hot.useNumberFormat = true;
				$('.use-number-format-options').show();
			} else {
				app.Editor.Hot.useNumberFormat = false;
				$('.use-number-format-options').hide();
			}
			editor.render();
		}).trigger('change');

		formSettings.find('[name="numberFormat"]').on('change', function(event) {
			event.preventDefault();
			editor.render();
		});

		// Set currency
		formSettings.find('[name="currencyFormat"]').on('change', function(event) {
			event.preventDefault();
			$('.currency-format').attr('data-format', $.trim($(this).val()));
			editor.render();
		});

		// Set percent
		formSettings.find('[name="percentFormat"]').on('change', function(event) {
			event.preventDefault();
			$('.percent-format').attr('data-format', $.trim($(this).val()));
			editor.render();
		});

		// Set date
		formSettings.find('[name="dateFormat"]').on('change', function(event) {
			event.preventDefault();
			$('.date-format').attr('data-format', $.trim($(this).val()));
		});

		head.on('change ifChanged', function() {
			if(!$(this).is(':checked') && fixedHead.is(':checked')) {
				fixedHead.iCheck('uncheck');
			}
		});
		foot.on('change ifChanged', function() {
			if(!$(this).is(':checked') && fixedFoot.is(':checked')) {
				fixedFoot.iCheck('uncheck');
			}
		});
		fixedHead.on('change ifChanged', function() {
			var head = $('#table-elements-head');

			if($(this).is(':checked') && !head.is(':checked')) {
				head.iCheck('check');
			}
		});
		fixedFoot.on('change ifChanged', function() {
			var foot = $('#table-elements-foot');

			if($(this).is(':checked') && !foot.is(':checked')) {
				foot.iCheck('check');
			}
		});
		$('.features-fixed-header-footer').on('change ifChanged', function() {
			if($('.features-fixed-header-footer').is(':checked')) {
				$('.features-fixed-height').fadeIn();
			} else {
				$('.features-fixed-height').fadeOut();
			}
		});

		function initafterTableLoadedScript() {
			if($('#enable-after-table-loaded-script').is(':checked')) {
				$('#after-table-loaded-script').fadeIn();
			} else {
				$('#after-table-loaded-script').fadeOut();
			}
		}

		$('#enable-after-table-loaded-script').on('change ifChanged', function() {
			initafterTableLoadedScript();
		});

		initafterTableLoadedScript();

		$('[data-toggle="tooltip"]').tooltip();

		$('#stbCopyTextCodeExamples').change(function(){
			$('.stbCopyTextCodeShowBlock').hide().filter('[data-for="'+ jQuery(this).val()+ '"]').show();
		}).trigger('change');

		$('input[name="stbCopyTextCode"]').click(function(){
			this.select();
		});

		// Pro notify
		var $notification = $('#proPopup').dialog({
			autoOpen: false,
			width:    480,
			modal:    true,
			buttons:  {
				Close: function () {
					$(this).dialog('close');
				}
			}
		});
		$editableFieldProFeatureDialog = $('#editableFieldProFeatureDialog').dialog({
			autoOpen: false,
			width:    480,
			modal:    true,
			buttons:  {
				Close: function () {
					$(this).dialog('close');
				}
			}
		});
		$addDiagramProFeatureDialog = $('#addDiagramProFeatureDialog').dialog({
			autoOpen: false,
			width:    913,
			height:   'auto',
			modal:    true,
			buttons:  {
				Close: function () {
					$(this).dialog('close');
				}
			}
		});
		$('.pro-notify').on('click', function () {
			$notification.dialog('open');
		});
		$('#editableFieldProFeature').on('click', function(event) {
			event.preventDefault();
			$editableFieldProFeatureDialog.dialog('open');
		});
		$('#addDiagramProFeature').on('click', function(event) {
			event.preventDefault();
			$addDiagramProFeatureDialog.dialog('open');
		});
		$('#previewDiagramProFeature [data-tabs] a').on('click', function(event) {
			event.preventDefault();

			var dialog = $('#previewDiagramProFeature');

			dialog.find('[data-tabs] a').removeClass('active');
			dialog.find('[data-tab]').removeClass('active');

			$(this).addClass('active');
			dialog.find('[data-tab="' + $(this).attr('href') + '"]').addClass('active');
		});

		// Functions
		function initializeEditor() {
			var container = document.getElementById('tableEditor');

			return new Handsontable(container, {
				rowHeaders: 			true,
				colHeaders: 			true,
				height:  				windowHeight,
				renderAllRows: 			false,		// To prevent losing of rows for huge tables (need to check in future is it all right now?)
				colWidths:             	100,
				comments:              	true,
				contextMenu:           	true,
				formulas:              	true,
				manualColumnResize:    	true,
				manualRowResize:       	true,
				mergeCells:            	true,
				outsideClickDeselects: 	false,
				renderer:              	Handsontable.renderers.DefaultRenderer,
				startCols:             	app.getParameterByName('cols') || 5,
				startRows:             	app.getParameterByName('rows') || 5,
				currentRowClassName: 	'current',
				currentColClassName: 	'current'
			});
		}

		function initializeTabs() {
			var $rows = $('.row-tab'),
				$buttons = $('.subsubsub.tabs-wrapper .button'),
				current = $buttons.filter('.current').attr('href');

			$rows.filter(current).addClass('active');

			$buttons.on('click', function (e) {
				e.preventDefault();

				var $button = $(this),
					current = $button.attr('href');

				$rows.removeClass('active');
				$buttons.filter('.current').removeClass('current');
				$button.addClass('current');
				$rows.filter(current).addClass('active');

				switch(current) {
					case '#row-tab-editor':
						editor.render();
						break;
					case '#row-tab-preview':
						saveTable('#table-preview');
						break;
					default:
						break;
				}
			});
		}

		function initializeSettingsTabs () {
			var $rows = $('.row-settings-tab'),
				$buttons = $('.subsubsub.tabs-settings-wrapper .button'),
				current = $buttons.filter('.current').attr('href');

			$rows.filter(current).addClass('active');

			$buttons.on('click', function (e) {
				e.preventDefault();

				var $button = $(this),
					current = $button.attr('href');

				$rows.removeClass('active');
				$buttons.filter('.current').removeClass('current');
				$button.addClass('current');
				$rows.filter(current).addClass('active');
			});
		}

		function saveTable(preview) {
			preview = typeof(preview) != 'undefined' ? preview : false;

			var byPart = true,
				id = app.getParameterByName('id'),
				formData = $('form#settings'),
				metaData = [],
				rowsData = [],
				columnsWidth = [],
				afterTableLoadedScript = b64EncodeUnicode(formData.find('#after-table-loaded-script').val());

			if(preview) {
				var tableInstance = app.getTableInstanceById(id);

				if(tableInstance) {
					tableInstance.api().destroy();
					$(preview).empty();
				}
			}

			function b64EncodeUnicode(str) {
				return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
					return String.fromCharCode('0x' + p1);
				}));
			}

			app.createSpinner($('#buttonSave'));
			formData.find('input[name="elements[descriptionText]"]').val( formData.find('#descriptionText').val() );	// We need to put table description into the hidden field before the saving of table settings
			formData.find('input[name="features[after_table_loaded_script]"]').val(afterTableLoadedScript);

			$.each(editor.getData(), function (x, row) {
				var currentRow = { cells: [] };

				$.each(row, function (y, cell) {
					var meta = editor.getCellMeta(x, y),
						classes = [],
						rowData = {
							data: cell,
							calculatedValue: null,
							hidden: editor.mergeCells.mergedCellInfoCollection.getInfo(x, y) !== undefined,
							hiddenCell: meta.className && meta.className.match('hiddenCell') !== null
						};

					// Set cell format
					rowData.type = meta.type ? meta.type : 'text';
					rowData.formatType = meta.formatType ? meta.formatType : '';

					switch(rowData.formatType) {
						case 'currency':
							rowData.format = formData.find('[name="currencyFormat"]').val();
							break;
						case 'percent':
							rowData.format = formData.find('[name="percentFormat"]').val();
							break;
						case 'date':
							rowData.format = formData.find('[name="dateFormat"]').val();

							var date = moment(rowData.data, rowData.format);

							if (date.isValid()) {
								rowData.dateOrder = date.format('x');
							}
							break;
						default:
							rowData.format = meta.format;
							break;
					}

					// Set calculated value for cells with formulas
					if (app.Models.Tables.isFormula(cell)) {
						var value = app.Models.Tables.getFormulaResult(cell, x, y);

						if (value !== undefined) {
							if (!isNaN(value) && value !== '0' && value !== 0 && value % 1 !== 0) {	// round float
								var floatValue = parseFloat(value);

								if (floatValue.toString().indexOf('.') !== -1) {
									var afterPointSybolsLength = floatValue.toString().split('.')[1].length;

									if (afterPointSybolsLength > 4) {
										value = floatValue.toFixed(4);
									}
								}
							}
							rowData.calculatedValue = value;
						}
					}

					// Set classes for cell
					if (meta.className !== undefined) {
						$.each(meta.className.split(' '), function (index, element) {
							if (element.length) {
								classes.push($.trim(element));
							}
						});
					}
					rowData.meta = classes;

					// Set comments for cell
					if (typeof(meta.comment) != 'undefined') {
						rowData.comment = meta.comment;
					}

					// Set column width by cells of first table row
					if (x == 0) {
						columnsWidth.push(editor.getColWidth(y));
					}

					currentRow.cells.push(rowData);
				});

				// Row height
				currentRow.height = editor.getRowHeight(x);

				if (currentRow.height === undefined || parseInt(currentRow.height) < 10) {
					currentRow.height = null;
				}

				rowsData.push(currentRow);
			});

			metaData = {
				mergedCells: editor.mergeCells.mergedCellInfoCollection,
				columnsWidth: columnsWidth,
				css: ace.getValue()
			};

			// Request to save settings, meta and rows
			$.when(
				app.Models.Tables.setSettings(id, formData),
				app.Models.Tables.setHistorySettings(id, $('form#history-settings')),
				app.Models.Tables.setMeta(id, metaData)
			).then(
				function() {
					app.Models.Tables.setRows(id, rowsData, byPart, preview);
				}
			);
		}

		function generateWidthData() {
			if (! editor.allWidths) {
				if(typeof(editor.getSettings().colWidths) == 'object') {
					editor.allWidths = editor.getSettings().colWidths;
				} else {
					editor.allWidths = [];
				}
			}
		}
	});

}(window.jQuery, window.supsystic.Tables));