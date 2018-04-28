# CodeIgniter Active Record Plus

CodeIgniter's [Active Record](https://www.codeigniter.com/userguide2/database/active_record.html) doesn't mandate each database table have it's own class file. While that allows you to interact with databases with minimal scripting, you will find yourself writing a lot of redudant codes as your application grows bigger.

CodeIgniter Active Record Plus allows you to map your tables to their own class files and allows you to perform CRUD operations even more easily.

### Setting Up

1. Download the files and add them to your CodeIgniter installation.

2. Ensure the *subclass_prefix* is `MY_` in `application/config/config.php`

   ```php
   $config['subclass_prefix'] = 'MY_';
   ```

### Usage

The download includes the following example classes.

- *application/classes/records/ExampleUserRecord.class.php*
- *applicaiton/classes/items/ExampleUserItem.class.php*

**Record** is a class mimics your database table. The properties are mapped to database table fields.

**Item** is an extension of *Record*. *Record*s are a pure mapping of your database table. To use those values in views and controllers, you might need to further process them. *Item* are supposed to help you with that. While you can perform CRUD operations with just *Records* it is a good practice to have equivalent *Item* classes for every records.



```php
// CREATE
$newUser = new ExampleUserItem();
$newUser->user_username = 'rajbdilip';
$newUser->user_password = sha1('AStr0ngP@ssw0rd');
$newUser->user_fname = 'Dilip';
$newUser->user_lname = 'Baral';
$newUser->save();

// READ
$someUser = new ExampleUserItem();
$someUser->user_username = 'rajbdilip';
$someUser->get();
$someUser->process();

echo $someUser->getFullName();

// UPDATE
$someUser->user_fname = 'Dilip Raj';
$someUser->save();

// DELETE
$someUser->delete();

// DELETE (Example 2: multiple delete)
$deleteUseParam = new ExampleUserItem();
$deleteUserParam->user_lname = 'Baral';
$deleteUserParam->delete(); // Deletes all user records with 'Baral' as last name

```



#### Advanced usage	

For adanced queries, make your models extend *MY_Model* instead of *CI_Model* and use their methods.

```php
class User_model extends MY_Model {
    
    public function getUsersByLastName($lastName) {
        $this->db->like('user_lname', $lastName);
        //.. other where conditions
        $users = $this->getSelectedRecordsAsItems(ExampleUserItem:class);
        
        // $users['totalItems'] gives total number of matched records
        return $users['items'];
    }
}
```

