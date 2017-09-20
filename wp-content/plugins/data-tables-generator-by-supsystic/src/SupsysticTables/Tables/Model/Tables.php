<?php


class SupsysticTables_Tables_Model_Tables extends SupsysticTables_Core_BaseModel
{
    /**
     * Returns table column by index.
     * @param int $id Table id
     * @param int $index Column index
     * @return stdClass|null
     */
    public function getColumn($id, $index)
    {
        $query = $this->getColumnQuery($id)
            ->where($this->getField('columns', 'index'), '=', (int)$index);

        $column = $this->db->get_row($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }

        return $column;
    }

    /**
     * Returns the array of the NOT extended tables
     *
     * @return null|array
     */
    public function getList()
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->db->prefix . 'supsystic_tbl_tables')
            ->orderBy('id')
            ->order('DESC');

        return $this->db->get_results($query->build());
    }

    /**
     * Returns an array of the table columns.
     * @param int $id Table id
     * @return string[]
     */
    public function getColumns($id)
    {
        $query = $this->getColumnQuery($id)
            ->orderBy($this->getField('columns', 'index'));

        $columns = $this->db->get_results($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }

        if (count($columns) > 0) {
            foreach ($columns as $index => $column) {
                $columns[$index] = $column->title;
            }
        }

        return $columns;
    }

    /**
     * Adds a new column to the table.
     * @param int $id Table id
     * @param array|object $column Column data (index, title)
     */
    public function addColumn($id, $column)
    {
        if (!is_array($column) && !is_object($column)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Second parameter must be an array or an instance of the stdClass, %s given.',
                    gettype($column)
                )
            );
        }

        $column = (array)$column;
        if (!array_key_exists('table_id', $column)) {
            $column['table_id'] = (int)$id;
        }

        foreach ((array)$column as $key => $value) {
            unset($column[$key]);
            $column[$this->getField('columns', $key)] = $value;
        }

        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable('columns'))
            ->fields(array_keys($column))
            ->values(array_values($column));

        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }
    }

    /**
     * Updates column data.
     * @param int $id Table id
     * @param int $index Column index
     * @param array|object $column Column data
     */
    public function setColumn($id, $index, $column)
    {
        if (!is_array($column) && !is_object($column)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Second parameter must be an array or an instance of the stdClass, %s given.',
                    gettype($column)
                )
            );
        }

        $column = (array)$column;

        $query = $this->getQueryBuilder()
            ->update($this->getTable('columns'))
            ->fields(array_keys($column))
            ->values(array_values($column))
            ->where($this->getField('columns', 'table_id'), '=', (int)$id)
            ->andWhere($this->getField('columns', 'index'), '=', (int)$index);

        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }
    }

    /**
     * Removes old columns and set a net columns for the table.
     * @param int $id Table id
     * @param array $columns An array of the columns with data.
     */
    public function setColumns($id, array $columns)
    {
        if (count($columns) === 0) {
            throw new InvalidArgumentException('Too few columns.');
        }

        try {
            $this->removeColumns($id);

            foreach ($columns as $index => $column) {
                if (is_string($column)) {
                    $column = array('title' => $column);
                }

                $column = (array)$column;

                if (is_array($column) && !array_key_exists('index', $column)) {
                    $column['index'] = $index;
                }

                $this->addColumn($id, (array)$column);
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf(
                    'Failed to set columns: %s',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Removes table columns.
     * @param int $id Table id
     */
    public function removeColumns($id)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable('columns'))
            ->where('table_id', '=', (int)$id);

        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }
    }

    /**
     * Adds a row to the table.
     * @param int $id Table id
     * @param array $data An array of the row data
     * @return int
     */
    public function addRow($id, array $data)
    {
        $data = $this->prepareRowsData($data);

        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable('rows'))
            ->fields(
                $this->getField('rows', 'table_id'),
                $this->getField('rows', 'data')
            )
            ->values((int)$id, serialize($data));

        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }

        return $this->db->insert_id;
    }

	public function prepareRowsData($data, $compress = true)
	{
		if (!empty($data['cells'])) {
			$keys = array(
				'd' => 'data',
				'cv' => 'calculatedValue',
				'h' => 'hidden',
				'hc' => 'hiddenCell',
				't' => 'type',
				'f' => 'format',
				'ft' => 'formatType',
				'do' => 'dateOrder',
				'm' => 'meta',
				'c' => 'comment',
			);

			if($compress) {
				foreach ($data['cells'] as &$cell) {
					if (isset($cell['comment']) && isset($cell['comment']['value'])) {
						$cell['comment']['value'] = htmlspecialchars($cell['comment']['value'], ENT_QUOTES);
					}
					if (!empty($cell['calculatedValue'])) {
						$cell['calculatedValue'] = htmlspecialchars((string)$cell['calculatedValue'], ENT_QUOTES);
					}
					$cell['data'] = htmlspecialchars((string)$cell['data'], ENT_QUOTES);
				}
				foreach ($data['cells'] as &$cell) {
					foreach ($keys as $key => $val) {
						if(array_key_exists($val, $cell)) {
							$cell[$key] = $cell[$val];
							unset($cell[$val]);
						}
					}
				}
			} else {
				foreach ($data['cells'] as &$cell) {
					foreach ($keys as $key => $val) {
						if(array_key_exists($key, $cell)) {
							$cell[$val] = $cell[$key];
							unset($cell[$key]);
						}
					}
				}
				foreach ($data['cells'] as &$cell) {
					if (isset($cell['comment']) && isset($cell['comment']['value'])) {
						$cell['comment']['value'] = htmlspecialchars_decode($cell['comment']['value'], ENT_QUOTES);
					}
					if (!empty($cell['calculatedValue'])) {
						$cell['calculatedValue'] = htmlspecialchars_decode($cell['calculatedValue'], ENT_QUOTES);
					}
					$cell['data'] = htmlspecialchars_decode($cell['data'], ENT_QUOTES);
				}
			}
		} else {
			$data['cells'] = array();
		}

		return $data;
	}

    /**
     * Returns all table rows
     * @param int $id Table id
     * @return array
     */
    public function getRows($id)
    {
        $query = $this->getQueryBuilder()
            ->select($this->getField('rows', 'data'))
            ->from($this->getTable('rows'))
            ->where('table_id', '=', (int)$id)
            ->orderBy($this->getField('rows', 'id'));
        
        $rows = $this->db->get_results($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }

        if (count($rows) > 0) {
            foreach ($rows as $index => $row) {
                $rows[$index] = @unserialize($row->data);
            }
        }

        foreach ($rows as &$row) {
			$row = $this->prepareRowsData($row, false);

        }

        return $rows;
    }

	/**
	 * Sets the part of rows for the table
	 * @param int $id Table id
	 * @param array $rows An array of the rows
	 */
	public function setRowsByPart($id, array $rows, $step, $last)
	{
		if (count($rows) === 0) {
			throw new InvalidArgumentException('Too few rows.');
		}
		$option_name = $this->environment->getConfig()->get('db_prefix') . 'last_row_id_' . $id;

		try {
			if(!$lastRowId = get_option($option_name)) {
				$query = $this->getQueryBuilder()
					->select('MAX(' . $this->getField('rows', 'id') . ') as max')
					->from($this->getTable('rows'))
					->where($this->getField('rows', 'table_id'), '=', (int)$id);

				$lastRowId = $this->db->get_results($query->build());
				$lastRowId = $lastRowId[0]->max;
				update_option($option_name, $lastRowId);
			}
			$this->removeRowsByPart($id, $lastRowId, $step, $last);

			foreach ($rows as $row) {
				$this->addRow($id, $row);
			}

			if($last) {
				$this->removeRowsByPart($id, $lastRowId, $last);
				delete_option($option_name, $lastRowId);
			}

		} catch (Exception $e) {
			throw new RuntimeException(
				sprintf('Failed to set rows: %s', $e->getMessage())
			);
		}
	}

	public function removeRowsByPart($id, $lastRowId, $step, $last = false)
	{
		$query = $this->getQueryBuilder()
			->deleteFrom($this->getTable('rows'))
			->where($this->getField('rows', 'table_id'), '=', (int)$id)
			->andWhere($this->getField('rows', 'id'), '<=', (int)$lastRowId);

		if(!$last) {
			$query->limit((int)$step);
		}

		$this->db->query($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
	}

    /**
     * Sets the rows for the table
     * @param int $id Table id
     * @param array $rows An array of the rows
     */
    public function setRows($id, array $rows, $remove = true)
    {
        if (count($rows) === 0) {
            throw new InvalidArgumentException('Too few rows.');
        }

        try {
			if($remove) {
				$this->removeRows($id);
			}

            foreach ($rows as $row) {
                $this->addRow($id, $row);
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('Failed to set rows: %s', $e->getMessage())
            );
        }
    }

    /**
     * Removes all table rows.
     * @param int $id Table id
     */
    public function removeRows($id)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable('rows'))
            ->where($this->getField('rows', 'table_id'), '=', (int)$id);


        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }
    }

    public function setMeta($id, array $meta)
    {
        $query = $this->getQueryBuilder()
            ->update($this->getTable())
            ->where('id', '=', (int)$id)
            ->set(array('meta' => serialize($meta)));


        $this->db->query($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }
    }

    /**
     * Callback for SupsysticTables_Tables_Model_Tables::get()
     * @see SupsysticTables_Tables_Model_Tables::get()
     * @param object|null $table Table data
     * @return object|null
     */
    public function onTablesGet($table)
    {
        // This method load twice all rows in backend second call go via ajax.
        // Need to fix.
        if (null === $table) {
            return $table;
        }

		$table->view_id = $table->id . '_' . mt_rand(1, 99999);
        $table->columns = $this->getColumns($table->id);
        $table->rows = $this->getRows($table->id);
        $table->settings = unserialize(htmlspecialchars_decode($table->settings));

        // rev 41
        if (property_exists($table, 'meta')) {
            $table->meta = unserialize(htmlspecialchars_decode($table->meta));
        }
        
        return $table;
    }

    /**
     * Filter for SupsysticTables_Tables_Model_Tables::getAll()
     * @see SupsysticTables_Tables_Model_Tables::getAll()
     * @param object[] $tables An array of the tables data
     * @return object[]
     */
    public function onTablesGetAll($tables)
    {
        if (null === $tables || (is_array($tables) && count($tables) === 0)) {
            return $tables;
        }

        return array_map(array($this, 'onTablesGet'), $tables);
    }

    /**
     * {@inheritdoc}
     *
     * Adds filters for the methods get() and getAll().
     */
    public function onInstanceReady()
    {
        parent::onInstanceReady();

        $dispatcher = $this->environment->getDispatcher();


        $dispatcher->on('tables_get', array($this, 'onTablesGet'));
        $dispatcher->on('tables_get', array($this, 'onTablesGetPro'));
        // No reason to fetch all data from all tables when we need only tables list
        // $dispatcher->on('tables_get_all', array($this, 'onTablesGetAll'));
    }

    protected function getColumnQuery($id)
    {
        return $this->getQueryBuilder()
            ->select($this->getField('columns', 'title'))
            ->from($this->getTable('columns'))
            ->where('table_id', '=', (int)$id);
    }

    public function getSettings($id)
    {
        $query = $this->getQueryBuilder()
            ->select($this->getField('tables', 'settings'))
            ->from($this->getTable('tables'))
            ->where('id', '=', (int)$id);

        $result = $this->db->get_results($query->build());

        if ($this->db->last_error) {
            throw new RuntimeException($this->db->last_error);
        }

        return $result;
    }

	// Hisoty methods for PRO version
	public function getAllTableHistory($tableId)
	{
		$query = $this->getQueryBuilder()
			->select('*')
			->from($this->getTable('rows_history'))
			->where('table_id', '=', (int)$tableId);

		$history = $this->db->get_results($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
		for($i = 0; $i < count($history); $i++) {
			$history[$i] = $this->_afterSimpleGet($history[$i]);
		}

		return $history;
	}

	public function getUserTableHistory($userId, $tableId)
	{
		$query = $this->getQueryBuilder()
			->select('data')
			->from($this->getTable('rows_history'))
			->where('table_id', '=', (int)$tableId)
			->andWhere('user_id', '=', (int)$userId);

		$history = $this->db->get_row($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
		if(!$history) {
			$this->createUserTableHistory($userId, $tableId);

		}
		$history = $this->db->get_row($query->build());
		$history = $this->_afterSimpleGet($history);

		return $history;
	}

	public function updateUserTableHistory($userId, $tableId, $data)
	{
		for($i = 0; $i < count($data); $i++) {
			$data[$i] = $this->prepareRowsData($data[$i], true);
		}
		$history = array(
			'data' => serialize($data),
			'created' => date('Y-m-d H:i:s')
		);
		$query = $this->getQueryBuilder()
			->update($this->getTable('rows_history'))
			->fields(array_keys($history))
			->values(array_values($history))
			->where('user_id', '=', (int) $userId)
			->andWhere('table_id', '=', (int) $tableId);
		$this->db->get_results($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
	}

	public function createUserTableHistory($userId, $tableId)
	{
		$history = array(
			'user_id' => $userId,
			'table_id' => $tableId,
			'data' => array()
		);
		$query = $this->getQueryBuilder()
			->select('data')
			->from($this->getTable('rows'))
			->where('table_id', '=', (int)$tableId);
		$rows = $this->db->get_results($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
		if (!$rows) {
			throw new RuntimeException(sprintf('The table with ID %d not exists.', $tableId));
		}

		for($i = 0; $i < count($rows); $i++) {
			array_push($history['data'], unserialize($rows[$i]->data));
		}
		$history['data'] = serialize($history['data']);

		$query = $this->getQueryBuilder()
			->insertInto($this->getTable('rows_history'))
			->fields(array_keys($history))
			->values(array_values($history));
		$this->db->get_results($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
	}

	public function _afterSimpleGet($history) {
		$history->data = unserialize($history->data);

		for($i = 0; $i < count($history->data); $i++) {
			$history->data[$i] = $this->prepareRowsData($history->data[$i], false);
		}

		return $history;
	}

	public function setHistorySettings($id, $settings) {
		$query = $this->getQueryBuilder()
			->update($this->getTable())
			->where('id', '=', (int)$id)
			->set(array('history_settings' => serialize($settings)));


		$this->db->query($query->build());

		if ($this->db->last_error) {
			throw new RuntimeException($this->db->last_error);
		}
	}
	public function onTablesGetPro($table)
	{
		// This method load twice all rows in backend second call go via ajax.
		// Need to fix.
		if (null === $table) {
			return $table;
		}
		if(!empty($table->history_settings)) {
			$table->historySettings = unserialize(htmlspecialchars_decode($table->history_settings));
		}

		return $table;
	}
}