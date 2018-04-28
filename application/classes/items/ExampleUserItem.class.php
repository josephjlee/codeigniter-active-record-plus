<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'classes/records/ExampleUserRecord.class.php';

/**
 * Class ExampleUserItem
 */
class ExampleUserItem extends ExampleUserRecord  {

    /**
     * @var string  User's full name
     */
    private $user_full_name;

    // /**
    //  * @var AddressItem User's address
    //  */
    // private $address;


    /**
     * Process data so that they can be used in views
     */
    public function process() {
        $this->user_username = $this->user_fname . " " . $this->user_lname;

        // Other information processing; Say retrieve the user's address which is an AddressItem which extends
        // AddressRecord
    }

    public function getFullName() {
        return $this->user_full_name;
    }

}
