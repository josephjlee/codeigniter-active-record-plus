<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Record
 *
 * Mimics a database table row.
 *
 * This is used mainly for CRUD operations. `Item` classes extends it for further
 * processing of data to use with views.
 *
 * To create a record class for a table, this class should be extended. An
 * equivalent public property should be created for each field in the table.
 * The property name should match the field name. No other properties may be
 * defined.
 *
 * Read more about Active Record pattern: https://en.wikipedia.org/wiki/Active_record_pattern
 */
abstract class Record {

    /**
     * @var CI_Model
     */
    private $model;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $uniqueKey;

    public function __construct() {
        $this->model = new MY_Model();

        $this->table = $this->getTableName();
        $this->uniqueKey = $this->getPrimaryKey();
    }

    /**
     * Returns the name of the table
     *
     * @return string
     */
    abstract public function getTableName();

    /**
     * Returns the primary key of the table
     *
     * @return string
     */
    abstract public function getPrimaryKey();

    /**
     * Fetch matching records from the table.
     *
     * Returns a instance of Record when $onlyOne is passed true.
     * Returns an array of Record instances otherwise.
     *
     * @param boolean   $onlyOne
     * @param string    $cols
     * @param int       $pageNo
     * @param int       $limit
     *
     * @return Record | array | null | bool
     */
    public function get($onlyOne = true, $cols = '*', $pageNo = null, $limit = null) {
        if ($onlyOne) {
            $record = $this->model->getRecordsFromDB($this->table, $this->toArray(), true, $cols, false, $pageNo, $limit);
            if (!$record) {
                return null;
            }
            // Assign the record properties to this object
            foreach ($record as $key => $value) {
                $this->{$key} = $value;
            }
            return true;
        } else {
            return $this->model->getRecordsFromDB($this->table, $this->toArray(), false, $cols, false, $pageNo, $limit);
        }
    }

    /**
     * Save or update the record to the table.
     *
     * If the primary key is present the record will be updated. Otherwise a new
     * record will be created.
     *
     * @return int | null
     */
    public function save() {
        return $this->model->saveOrUpdateRecordToDB($this->table, $this->uniqueKey, $this);
    }

    /**
     * Delete the record from the table.
     *
     * @return int  Number of affected rows
     */
    public function delete() {
        $table = $this->getTableName();

        return $this->model->deleteRecordFromDB($table, $this->toArray());
    }

    /**
     * Converts a Record instance to an array.
     * Properties with null values are excluded from the resulting array.
     *
     * @return array
     */
    public function toArray() {
        $arr = array();
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $key = $property->getName();
            $value = $property->getValue($this);
            if ($value != null || (is_numeric($value) && $value == 0)) {
                $arr[$key] = $value;
            }
        }

        return $arr;
    }
}