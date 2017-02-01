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