<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'classes/Record.class.php';
require_once APPPATH . 'classes/util/ClassUtil.class.php';

class MY_Model extends CI_Model {

    /**
     * @var int
     *
     * Total rows matched by the given condition
     */
    protected $totalMatchedRowsInDB = null;

    /**
     * Returns selected DB records as items
     * It is mandatory to have selected records before calling this method
     *
     * @param string
     * @param int
     * @param int
     *
     * @return array
     */
    public function getSelectedRecordsAsItems($itemClassName, $pageNo = null, $limit = null) {
        $items = array();

        $recordClassName = get_parent_class($itemClassName);
        $tableName = (new $recordClassName())->getTableName();

        require_once APPPATH . "classes/records/$recordClassName.class.php";
        require_once APPPATH . "classes/items/$itemClassName.class.php";

        $this->db->from($tableName);
        $result = $this->getAllSelectedRecordsFromDB($pageNo, $limit);
        $totalRows = $this->totalMatchedRowsInDB;

        if ($result) {
            foreach ($result as $row) {
                $record = ClassUtil::arrayToObject($row, $recordClassName);
                $item = ClassUtil::castToDerivedClass($record, $itemClassName);
                $item->process();

                $items[] = $item;
            }
        }

        return array(
            'totalRows' => $totalRows,
            'items'	    => $items,
        );
    }

    /**
     * Insert or update single record into table
     *
     * @param string $table
     *      Table name
     * @param string $uniqueKey
     *      Unique identifier of the record
     * @param Record | array $data
     *      Array of data to be inserted or updated
     *
     * @return int | null
     *      ID of the inserted or update ID
     *
     */
    public function saveOrUpdateRecordToDB($table, $uniqueKey, $data) {
        if ($data instanceof Record) {
            $data = $data->toArray();
        }

        $this->db->flush_cache();

        if (isset($data[$uniqueKey])) {
            // If a unique key is passed, update the record
            $recordID = $data[$uniqueKey];
            unset($data[$uniqueKey]);

            $this->db->where($uniqueKey, $recordID);
            if ($this->db->update($table, $data)) {
                return $recordID;
            }
        } else {
            if ($this->db->insert($table, $data)) {
                return $this->db->insert_id();
            }
        }
        return null;
    }

    /**
     * Get record(s) from table and return as a Record or an array
     *
     * @param string    $table
     *      Table name in snake_case or PascalCase
     * @param array     $param
     *      Where conditions
     * @param bool      $onlyOne
     *      Whether to select top one record. All matched records are returned by default
     * @param string    $columns
     *      Columns to be fetched from the table
     * @param bool      $toArray
     *      Whether to return result as an array. Result is returned as Record by default (recommended)
     * @param int       $pageNo
     * @param int       $limit
     *
     *
     * @return Record | array | null
     */
    public function getRecordsFromDB($table, $param, $onlyOne = false, $columns = '*', $toArray = false, $pageNo = null, $limit = null) {
        $this->db->flush_cache();

        $select = is_array($columns) ? implode(',', $columns) : $columns;
        $this->db->select($select);
        $this->db->from($table);

        // Where conditions
        foreach ($param as $key => $value) {
            $this->db->where($key, $value);
        }

        if ($onlyOne) {
            $limit = 1;
        }

        if ($toArray) {
            if ($onlyOne) {
                return $this->getOnlyOneSelectedRecordFromDB($pageNo);
            } else {
                return $this->getSelectedRecordsFromDB($pageNo, $limit);
            }
        } else {
            $recordClassName = snakeCaseToPascalCase($table) . 'Record';
            require_once APPPATH . "classes/records/$recordClassName.class.php";

            if ($onlyOne) {
                $result = $this->getOnlyOneSelectedRecordFromDB($pageNo);
                if ($result == null) {
                    return null;
                }
                return ClassUtil::arrayToObject($result, $recordClassName);
            } else {
                $result = $this->getSelectedRecordsFromDB($pageNo, $limit);
                if ($result == null) {
                    return null;
                }
                $bookRecords = array();
                foreach ($result as $bookRecord) {
                    $bookRecords[] = ClassUtil::arrayToObject($bookRecord, $recordClassName);
                }
                return $bookRecords;
            }
        }
    }

	/**
	 * Return all selected records from the database
	 *
     * @param int
     * @param int
     *
	 * @return array | null
	 */
	protected function getAllSelectedRecordsFromDB($pageNo = null, $limit = null) {
		return $this->getSelectedRecordsFromDB($pageNo, $limit);
	}

	/**
	 * Return top one record from the database
	 *
     * @param int
     * @param int
     *
	 * @return array | null
	 */
	protected function getOnlyOneSelectedRecordFromDB($pageNo = null) {
		return $this->getSelectedRecordsFromDB($pageNo, 1);
	}

    /*
    |--------------------------------------------------------------------------
    | LOW LEVEL DATABASE METHODS
    |--------------------------------------------------------------------------
    */

    /**
	 * Returns selected database records as array
	 * It is mandatory to have selected records before calling this method
	 * using $this->db->select(), etc.
	 * 
	 * @param int $pageNo
	 * 		Page number
	 * @param int $limit
	 * 		No. of records to be retreived
	 *
	 * @return array | null
	 * 		Returns null when there are no records,
	 * 		array of records otherwise
	 */
	protected function getSelectedRecordsFromDB($pageNo = null, $limit = null) {
		$this->totalMatchedRowsInDB = $this->db->count_all_results('', false);

		if (!$pageNo && $limit) {
            $this->db->limit($limit);
        } elseif ($pageNo && $limit) {
			$this->db->limit($limit, ($pageNo - 1) * $limit);
		}

		$query = $this->db->get();
		if ($query->num_rows() == 0) {
			return null;
		}

		if ($limit == 1) {
			return $query->row_array();
		} else {
			return $query->result_array();
		}
	}

    /**
     * Insert record to table
     *
     * @param $table
     * @param $data
     *
     * @return int | null
     */
    protected function insertRecordToDB($table, $data) {
	    $this->db->flush_cache();
	    if ($this->db->insert($table, $data)) {
            return $this->db->insert_id();
        }

        return null;
    }

    /**
     * Update an existing record to table
     *
     * @param $table
     * @param $param
     * @param $data
     *
     * @return int
     *      Number of affected rows
     */
    protected function updateRecordInDB($table, $param, $data) {
	    $this->db->flush_cache();
	    foreach ($param as $field => $value) {
	        $this->db->where($field, $value);
        }

        $this->db->update($table, $data);

	    return $this->db->affected_rows();
    }

    /**
     * Delete a record in table
     *
     * @param string $table
     * @param array $param
     *
     * @return int
     *      Number of affected rows
     */
    public function deleteRecordFromDB($table, $param) {
        $this->db->flush_cache();
        foreach ($param as $field => $value) {
            $this->db->where($field, $value);
        }

        $this->db->delete($table);

        return $this->db->affected_rows();
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Converts snake_case to PascalCase
     *
     * @param $string
     * @return mixed
     */
    function snakeCaseToPascalCase($string) {
        return str_replace('_', '', ucwords($string, '_'));
    }

}
