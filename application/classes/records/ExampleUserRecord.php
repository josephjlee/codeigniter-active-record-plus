<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'classes/Record.class.php';

class ExampleUserRecord extends Record {

    /**
     * @var integer Record identifier
     */
    public $user_id;

    /**
     * @var string  Unique username
     */
    public $user_username;

    /**
     * @var string  SHA hash of a password
     */
    public $user_password;

    /**
     * @var string  User's first name
     */
    public $user_fname;

    /**
     * @var string  User's last name
     */
    public $user_lname;

    public function getTableName() {
        return 'user';
    }

    public function getPrimaryKey() {
        return 'user_id';
    }

}