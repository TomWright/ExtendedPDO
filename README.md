# Extended PDO

[![Build Status](https://travis-ci.org/TomWright/ExtendedPDO.svg?branch=master)](https://travis-ci.org/TomWright/ExtendedPDO)
[![Latest Stable Version](https://poser.pugx.org/tomwright/extended-pdo/v/stable)](https://packagist.org/packages/tomwright/extended-pdo)
[![Total Downloads](https://poser.pugx.org/tomwright/extended-pdo/downloads)](https://packagist.org/packages/tomwright/extended-pdo)
[![Monthly Downloads](https://poser.pugx.org/tomwright/extended-pdo/d/monthly)](https://packagist.org/packages/tomwright/extended-pdo)
[![Daily Downloads](https://poser.pugx.org/tomwright/extended-pdo/d/daily)](https://packagist.org/packages/tomwright/extended-pdo)
[![License](https://poser.pugx.org/tomwright/extended-pdo/license.svg)](https://packagist.org/packages/tomwright/extended-pdo)

## Installation

```
composer install tomwright/extended-pdo
```

## Connecting
```php
$db = ExtendedPDO::createConnection($dsn, $username, $password, 'my-main-db');
$db2 = ExtendedPDO::getInstance('my-main-db');

var_dump($db === $db2) // TRUE
```

## Usage
```php
// Returns an array of records
$db->queryAll('SELECT * FROM users WHERE username = :username', [':username' => 'Tom']);

// Returns the first record
$db->queryRow('SELECT * FROM users WHERE username = :username LIMIT 1', [':username' => 'Tom']);
```

Both `queryRow()` and `queryAll()` are able to return the `\PDOStatement`.

To see more information on how to do this, see the [method arguments in the source code](src/ExtendedPDO.php#L78).

## Query Builder

ExtendedPDO also comes with it's own [Query Builder](stc/Query.php).

### Quick Examples

#### SELECT

    SELECT uea.email
    FROM users u
    JOIN user_email_addresses uea ON uesa.user_id = u.user_id
    WHERE uea.email_confirmed = 1
    AND ( uea.dt_deleted IS NULL OR uea.dt_deleted > NOW() )
    ORDER BY uea.dt_created ASC

In order to get the above query, you would do something like this:

    $query = new Query('SELECT')
        ->setFields(['uea.email'])
        ->setTable('users u')
        ->addJoin(new Join('JOIN', 'user_email_addresses uea', 'uea.user_id = u.user_id'))
        ->addWhere('uea.email_confirmed', true)
        ->addRawWhere('( uea.dt_deleted IS NULL OR uea.dt_deleted > NOW() )')
        ->addOrderBy('uea.dt_created ASC')
        ->buildQuery();
    
    $db->queryAll($query->getSql(), $query->getBinds());
        
#### UPDATE

    UPDATE users
    SET
        username = 'Tom',
        dt_modified = NOW()
    WHERE user_id = 5;

In order to get the above query, you would do something like this:

    $query = new Query('UPDATE')
        ->setValues('username', 'Tom')
        ->addRawValue('dt_modified', 'NOW()')
        ->setTable('users')
        ->addWhere('user_id', 5)
        ->buildQuery();
        
    $db->dbQuery($query->getSql(), $query->getBinds());

### Getting the SQL and Bind Parameters out of the Query Builder

In order to get the SQL and bind parameters, you must have already run `buildQuery()` on the `Query` object.

    $query->getSql();
    // SELECT * FROM users WHERE username = :_where_username;
    
    $query->getBinds();
    // [':_where_username' => 'Tom']

### Selecting the Query Type

    $query = new Query('SELECT'); // SELECT query
    $query = new Query('DELETE'); // DELETE query
    $query = new Query()->setType('UPDATE'); // UPDATE query
    
### Choosing fields to SELECT

The fields default to `['*']`.

    $query->setFields(['something', 'something_else']);
    $query->addField('another_field');
    
    $query->getFields(); // ['something', 'something_else', 'another_field']

### Choosing the table

    $query->setTable('users');
    $query->setTable('users u');
    
    $query->getTable('users u');

### Values to SET or UPDATE

Using the Query Builder here takes advantage of PDO bind parameters and makes you invulnerable to SQL Injection.

    $query->addValue('users.username', 'Tom');

### Raw Values

Using raw values will NOT use PDO bind parameters and so your SQL queries may be vulnerable to SQL Injection.

    $query->addRawValue('users.dt_registered', 'NOW()');

### Joins

Create an instance of the [Join class](stc/Join.php) and then add it to a Query.

    $query
        ->setTable('users')
        ->addJoin(new Join('JOIN', 'codes', 'codes.user_id = users.user_id'));

You can also use aliases here.

    $query
        ->setTable('users u')
        ->addJoin(new Join('JOIN', 'codes c', 'c.user_id = u.user_id'));

### Where's

Building WHERE statements using the Query Builder takes advantage of PDO bind parameters so your queries are protected against SQL Injection.

#### Comparison Types

The default comparison is `=`.

    $query->addWhere('users.username', 'Tom')
    
##### Custom Comparison
You can easily override the default comparison by doing the following.

    $query->addWhere('users.username !=', 'Tom')

#### Raw SQL in where's

When you use raw SQL you will not benefit from PDO bind parameters and your queries may be vulnerable to SQL Injection.

    $query->addRawWhere('users.dt_registered <= NOW()');
    
### Order Bys

    $query->addOrderBy('users.username ASC');
    
### Group Bys

    $query->addGroupBy('users.user_id');

### Limit

    $query->setLimit(5);
    
### Offset

    $query->setOffset(5);

### Pagination
    
The Query Builder has a handy method to slightly simplify pagination.
If you were on the 2nd page, and displayed 5 records per page it would look like the following.

    $query->setPage(2, 5);

In the background this simply sets a limit of 5 and an offset of 5.