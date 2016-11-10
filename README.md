# Extended PDO

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