## Opencart v3 database transaction

* PHP 7.4+

***Make sure your database storage engine (InnoDB for example) supports transactions***

### Installation
Upload the contents to the root directory of your OpenCart installation.

To start using DB transaction, we need replace old DB class in system/framework.php:

Find:
```
$db = new DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port'));
```
And replace to:
```
$db = \DbTransaction\Factory::create($config, 'pdo');
```
Should look like this
![alt text](https://i.ibb.co/qNpjq5j/Screenshot-2.jpg)

***Also in config.php should be set pdo driver***
```
define('DB_DRIVER', 'pdo');
```

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