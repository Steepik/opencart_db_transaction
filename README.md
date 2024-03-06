## Opencart v2 database transaction

***Make sure your database storage engine (InnoDB for example) supports transactions***

### Installation
Upload the contents to the root directory of your OpenCart installation.

To start using DB transaction, we need replace old DB class in index.php:

Find:
```
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
```
And replace to:
```
$config->set('db_hostname', DB_HOSTNAME);
$config->set('db_username', DB_USERNAME);
$config->set('db_password', DB_PASSWORD);
$config->set('db_database', DB_DATABASE);
$config->set('db_port', '3306');
$db = \DbTransaction\Factory::create($config, 'pdo');
```
Should look like this
![alt text](https://i.ibb.co/zxjmjdL/Screenshot-1.jpg)

### How to use

```
$this->db->transaction(function (\DbTransaction\Connection $db) {
    $db->query("UPDATE user SET amount = amount - 100 WHERE id = 1");
    $db->query("UPDATE user SET amount = amount + 100 WHERE id = 2");
});
```

### Errors
As we know opencart database class won't throw an exception if sql query has bad syntax so now our class throw an exception.
#### For example
```
$this->db->transaction(function (\DbTransaction\Connection $db) {
    $db->query("UPDATE user SET money = money - 100 WHERE id = 1");
    $db->query("UPDATE user SET money = money + 100 WHERE id = 2");
});
```

```
Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'money' in 'field list'
Error Code : 42S22
```