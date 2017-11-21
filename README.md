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

ExtendedPDO implements the singleton design pattern using [tomwright/singleton](https://github.com/TomWright/Singleton).
For more information on how the following code works, see the [documentation](https://github.com/TomWright/Singleton).

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

## Query Return Types
You can set the return type of the `dbQuery()`, `queryAll()` and `queryRow()` methods using `$db->setReturnType($x)` where `$x` is the return type you'd like to use.

Available return types are as follows:

- `ExtendedPDO::RETURN_TYPE_OBJECT` - Your results will be returned as objects
- `ExtendedPDO::RETURN_TYPE_ASSOC` - Your results will be returned as associative arrays
- `ExtendedPDO::RETURN_TYPE_STMT` - The statement object will be returned directly

You can also set a return type of `\PDO::FETCH_ASSOC` for example and it will override any of the above. This makes all of the standard `PDO` fetch types usable.

## Query Builder

ExtendedPDO also comes with it's own [Query Builder](https://github.com/TomWright/QueryBuilderPHP).
