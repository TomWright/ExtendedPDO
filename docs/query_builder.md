# Query Builder

The Query Builder should be used to generate SQL queries in an object orientated fashion, allowing multiple functions or objects to modify queries in any way they desire.

This works well in large applications and search functionality.

## Quick Examples

### SELECT

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
        
### UPDATE

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

## Getting the SQL and Bind Parameters out of the Query Builder

In order to get the SQL and bind parameters, you must have already run `buildQuery()` on the `Query` object.

    $query->getSql();
    // SELECT * FROM users WHERE username = :_where_username;
    
    $query->getBinds();
    // [':_where_username' => 'Tom']

## Selecting the Query Type

    $query = new Query('SELECT'); // SELECT query
    $query = new Query('DELETE'); // DELETE query
    $query = new Query()->setType('UPDATE'); // UPDATE query
    
## Choosing fields to SELECT

The fields default to `['*']`.

    $query->setFields(['something', 'something_else']);
    $query->addField('another_field');
    
    $query->getFields(); // ['something', 'something_else', 'another_field']

## Choosing the table

    $query->setTable('users');
    $query->setTable('users u');
    
    $query->getTable('users u');

## Values to SET or UPDATE

Using the Query Builder here takes advantage of PDO bind parameters and makes you invulnerable to SQL Injection.

    $query->addValue('users.username', 'Tom');

## Raw Values

Using raw values will NOT use PDO bind parameters and so your SQL queries may be vulnerable to SQL Injection.

    $query->addRawValue('users.dt_registered', 'NOW()');
    
## ON DUPLICATE KEY UPDATE

Sometimes you may need to use the `ON DUPLICATE KEY UPDATE` SQL syntax. This is achieved by doing the following.

    $query->addOnDupeValue('users.username', 'Tom');

A full query may look something like this.
    
    $q = new Query('INSERT');
    $q->setTable('users');
    $q->addValue('username', 'Tod');
    $q->addValue('password', 'abcdef');
    $q->addOnDupeValue('password', 'abcdef');
    $q->buildQuery();
    
    $q->getSql(); // INSERT INTO users SET username = :_update_bind_username, password = :_update_bind_password ON DUPLICATE KEY UPDATE password = :_dupe_update_bind_password;

## Joins

Create an instance of the [Join class](src/Join.php) and then add it to a Query.

    $query
        ->setTable('users')
        ->addJoin(new Join('JOIN', 'codes', 'codes.user_id = users.user_id'));

You can also use aliases here.

    $query
        ->setTable('users u')
        ->addJoin(new Join('JOIN', 'codes c', 'c.user_id = u.user_id'));

## Where's

Building WHERE statements using the Query Builder takes advantage of PDO bind parameters so your queries are protected against SQL Injection.

### Comparison Types

The default comparison is `=`.

    $query->addWhere('users.username', 'Tom')
    
#### Custom Comparison
You can easily override the default comparison by doing the following.

    $query->addWhere('users.username !=', 'Tom')

### Raw SQL in where's

When you use raw SQL you will not benefit from PDO bind parameters and your queries may be vulnerable to SQL Injection.

    $query->addRawWhere('users.dt_registered <= NOW()');
    
## Like

To save having lots of raw SQL when `LIKE` is concerned, you can use the `Like` object in your where clauses.

    SELECT * FROM users WHERE (username LIKE '%Tom%' OR username LIKE '%Jim%');

becomes...

    $like = new Like('contains', ['Tom', 'Jim']);

    $q = new Query('SELECT');
    $q->setTable('users');
    $q->addWhere('username', $like);
    
The different like types you can use are `contains`, `starts_with` and `ends_with`.
    
## Order Bys

    $query->addOrderBy('users.username ASC');
    
## Group Bys

    $query->addGroupBy('users.user_id');

## Limit

    $query->setLimit(5);
    
## Offset

    $query->setOffset(5);

## Pagination
    
The Query Builder has a handy method to slightly simplify pagination.
If you were on the 2nd page, and displayed 5 records per page it would look like the following.

    $query->setPage(2, 5);

In the background this simply sets a limit of 5 and an offset of 5.

## Sub Queries

If you are looking to use sub-queries but still want to use the query builder then you have come to the right place.

Using sub-queries in the following manner will still use PDO prepared statements so as your queries are always safe from SQL injection.

### Sub Queries in WHERE clause

Simply build your sub-query and pass that into the `where` method of another query using `%SQL%` as a replacement placeholder for the SQL.

Desired SQL query:
    
    SELECT * FROM users
    WHERE user_id IN (
            SELECT user_id
            FROM deleted_users
            WHERE deleted_users.deleted = 1
            AND username != 'Jim';
        )
    AND username != 'Tom';

PHP Code:

    $subQ = new Query('SELECT');
    $subQ->setTable('deleted_users');
    $subQ->setFields(['user_id']);
    $subQ->addWhere('deleted_users.deleted', true);
    $subQ->addWhere('username !=', 'Jim');
    
    $q = new Query('SELECT');
    $q->setTable('users');
    $q->addWhere('user_id IN (%SQL%)', $subQ);
    $q->addWhere('username !=', 'Tom');
    $q->buildQuery();