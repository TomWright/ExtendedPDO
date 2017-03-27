<?php


use PHPUnit\Framework\TestCase;
use TomWright\Database\ExtendedPDO\Join;
use TomWright\Database\ExtendedPDO\Query;

class QueryTest extends TestCase
{

    public function testQuerySelect()
    {
        $sql = 'SELECT * FROM users;';

        $q = new Query('SELECT');
        $q->setTable('users');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
    }

    public function testQuerySelectWheres()
    {
        $q = new Query('SELECT');
        $q->setTable('users');
        $q->addWhere('user_id', 5);
        $q->buildQuery();

        $sql = "SELECT * FROM users WHERE user_id = :_{$q->getQueryId()}_where_user_id;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_where_user_id" => 5,
        ], $q->getBinds());

        $q->addWhere('user_id', 5);
        $q->addWhere('username !=', 'Frank');
        $q->buildQuery();

        $sql = "SELECT * FROM users WHERE user_id = :_{$q->getQueryId()}_where_user_id AND username != :_{$q->getQueryId()}_where_username;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_where_user_id" => 5,
            ":_{$q->getQueryId()}_where_username" => 'Frank',
        ], $q->getBinds());

        $q->addWhere('user_id', 5);
        $q->addWhere('username !=', 'Frank');
        $q->addRawWhere('(age <= :min_age OR age >= :max_age)', 'email_like');
        $q->addBind(':min_age', 18);
        $q->addBind(':max_age', 23);
        $q->buildQuery();

        $sql = "SELECT * FROM users WHERE user_id = :_{$q->getQueryId()}_where_user_id AND username != :_{$q->getQueryId()}_where_username AND (age <= :min_age OR age >= :max_age);";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_where_user_id" => 5,
            ":_{$q->getQueryId()}_where_username" => 'Frank',
            ':min_age' => 18,
            ':max_age' => 23,
        ], $q->getBinds());

        $sql = 'SELECT * FROM users LIMIT 5 OFFSET 0;';

        $q = new Query('SELECT');
        $q->setTable('users');
        $q->setPage(1, 5);
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([], $q->getBinds());

        $sql = 'SELECT * FROM users LIMIT 5 OFFSET 10;';

        $q = new Query('SELECT');
        $q->setTable('users');
        $q->setPage(3, 5);
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([], $q->getBinds());
    }

    public function testQuerySelectJoins()
    {
        $sql = 'SELECT * FROM users JOIN user_groups ON users.user_id = user_groups.user_id;';

        $q = new Query('SELECT');
        $q->setTable('users');
        $q->addJoin(new Join('JOIN', 'user_groups', 'users.user_id = user_groups.user_id'));
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([], $q->getBinds());
    }

    public function testQuerySelectWhereSubQuery()
    {
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

        $sql = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM deleted_users WHERE deleted_users.deleted = :_{$subQ->getQueryId()}_where_deleted_users_deleted AND username != :_{$subQ->getQueryId()}_where_username;) AND username != :_{$q->getQueryId()}_where_username;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$subQ->getQueryId()}_where_deleted_users_deleted" => 1,
            ":_{$subQ->getQueryId()}_where_username" => 'Jim',
            ":_{$q->getQueryId()}_where_username" => 'Tom',
        ], $q->getBinds());
    }

    public function testQueryUpdate()
    {
        $q = new Query('UPDATE');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addWhere('username', 'Frank');
        $q->buildQuery();

        $sql = "UPDATE users SET username = :_{$q->getQueryId()}_update_bind_username WHERE username = :_{$q->getQueryId()}_where_username;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_update_bind_username" => 'Tod',
            ":_{$q->getQueryId()}_where_username" => 'Frank',
        ], $q->getBinds());

        $q = new Query('UPDATE');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addRawValue('dt_modified', 'NOW()');
        $q->addWhere('username', 'Frank');
        $q->buildQuery();

        $sql = "UPDATE users SET username = :_{$q->getQueryId()}_update_bind_username, dt_modified = NOW() WHERE username = :_{$q->getQueryId()}_where_username;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_update_bind_username" => 'Tod',
            ":_{$q->getQueryId()}_where_username" => 'Frank',
        ], $q->getBinds());
    }

    public function testQueryInsert()
    {
        $q = new Query('INSERT');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addValue('password', 'abcdef');
        $q->buildQuery();

        $sql = "INSERT INTO users SET username = :_{$q->getQueryId()}_update_bind_username, password = :_{$q->getQueryId()}_update_bind_password;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_update_bind_username" => 'Tod',
            ":_{$q->getQueryId()}_update_bind_password" => 'abcdef',
        ], $q->getBinds());
    }

    public function testQueryInsertOnDuplicateKeyUpdate()
    {
        $q = new Query('INSERT');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addValue('password', 'abcdef');
        $q->addOnDupeValue('password', 'abcdef');
        $q->buildQuery();

        $sql = "INSERT INTO users SET username = :_{$q->getQueryId()}_update_bind_username, password = :_{$q->getQueryId()}_update_bind_password ON DUPLICATE KEY UPDATE password = :_{$q->getQueryId()}_dupe_update_bind_password;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_update_bind_username" => 'Tod',
            ":_{$q->getQueryId()}_update_bind_password" => 'abcdef',
            ":_{$q->getQueryId()}_dupe_update_bind_password" => 'abcdef',
        ], $q->getBinds());
    }

    public function testQueryDelete()
    {
        $q = new Query('DELETE');
        $q->setTable('users');
        $q->addWhere('username', 'Tod');
        $q->buildQuery();

        $sql = "DELETE FROM users WHERE username = :_{$q->getQueryId()}_where_username;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_where_username" => 'Tod',
        ], $q->getBinds());
    }

    public function testQueryConvertInt()
    {
        $q = new Query('DELETE');
        $q->setTable('users');
        $q->addWhere('active', false);
        $q->buildQuery();

        $sql = "DELETE FROM users WHERE active = :_{$q->getQueryId()}_where_active;";

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ":_{$q->getQueryId()}_where_active" => 0,
        ], $q->getBinds());

        $q->addWhere('active', true);
        $q->buildQuery();

        $this->assertEquals([
            ":_{$q->getQueryId()}_where_active" => 1,
        ], $q->getBinds());

        $q->setConvertBoolToInt(false);
        $q->addWhere('active', false);
        $q->buildQuery();

        $this->assertEquals([
            ":_{$q->getQueryId()}_where_active" => false,
        ], $q->getBinds());

        $q->addWhere('active', true);
        $q->buildQuery();

        $this->assertEquals([
            ":_{$q->getQueryId()}_where_active" => true,
        ], $q->getBinds());
    }

}