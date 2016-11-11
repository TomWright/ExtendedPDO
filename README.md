# Extended PDO

[![Build Status](https://travis-ci.org/TomWright/ExtendedPDO.svg?branch=master)](https://travis-ci.org/TomWright/ExtendedPDO)
[![Latest Stable Version](https://poser.pugx.org/tomwright/extended-pdo/v/stable)](https://packagist.org/packages/tomwright/extended-pdo)
[![Total Downloads](https://poser.pugx.org/tomwright/extended-pdo/downloads)](https://packagist.org/packages/tomwright/extended-pdo)
[![Monthly Downloads](https://poser.pugx.org/tomwright/extended-pdo/d/monthly)](https://packagist.org/packages/tomwright/extended-pdo)
[![Daily Downloads](https://poser.pugx.org/tomwright/extended-pdo/d/daily)](https://packagist.org/packages/tomwright/extended-pdo)
[![License](https://poser.pugx.org/tomwright/extended-pdo/license.svg)](https://packagist.org/packages/tomwright/extended-pdo)

## Connecting
```php
$db = ExtendedPDO::createConnection($dsn, $username, $password, 'my-main-db');
$db2 = ExtendedPDO::getInstance('my-main-db');

var_dump($db === $db2) // TRUE
```

## Usage
```php
$db->queryAll('SELECT * FROM users WHERE username = :username', [':username' => 'Tom']);
```