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
        $sql = 'SELECT * FROM users WHERE user_id = :_where_user_id;';

        $q = new Query('SELECT');
        $q->setTable('users');
        $q->addWhere('user_id', 5);
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_where_user_id' => 5,
        ], $q->getBinds());

        $sql = 'SELECT * FROM users WHERE user_id = :_where_user_id AND username != :_where_username;';

        $q->addWhere('user_id', 5);
        $q->addWhere('username !=', 'Frank');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_where_user_id' => 5,
            ':_where_username' => 'Frank',
        ], $q->getBinds());

        $sql = 'SELECT * FROM users WHERE user_id = :_where_user_id AND username != :_where_username AND (age <= :min_age OR age >= :max_age);';

        $q->addWhere('user_id', 5);
        $q->addWhere('username !=', 'Frank');
        $q->addRawWhere('(age <= :min_age OR age >= :max_age)', 'email_like');
        $q->addBind(':min_age', 18);
        $q->addBind(':max_age', 23);
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_where_user_id' => 5,
            ':_where_username' => 'Frank',
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

    public function testQueryUpdate()
    {
        $sql = 'UPDATE users SET username = :_update_bind_username WHERE username = :_where_username;';

        $q = new Query('UPDATE');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addWhere('username', 'Frank');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_update_bind_username' => 'Tod',
            ':_where_username' => 'Frank',
        ], $q->getBinds());

        $sql = 'UPDATE users SET username = :_update_bind_username, dt_modified = NOW() WHERE username = :_where_username;';

        $q = new Query('UPDATE');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addRawValue('dt_modified', 'NOW()');
        $q->addWhere('username', 'Frank');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_update_bind_username' => 'Tod',
            ':_where_username' => 'Frank',
        ], $q->getBinds());
    }

    public function testQueryInsert()
    {
        $sql = 'INSERT INTO users SET username = :_update_bind_username, password = :_update_bind_password;';

        $q = new Query('INSERT');
        $q->setTable('users');
        $q->addValue('username', 'Tod');
        $q->addValue('password', 'abcdef');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_update_bind_username' => 'Tod',
            ':_update_bind_password' => 'abcdef',
        ], $q->getBinds());
    }

    public function testQueryDelete()
    {
        $sql = 'DELETE FROM users WHERE username = :_where_username;';

        $q = new Query('DELETE');
        $q->setTable('users');
        $q->addWhere('username', 'Tod');
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_where_username' => 'Tod',
        ], $q->getBinds());
    }

    public function testQueryConvertInt()
    {
        $sql = 'DELETE FROM users WHERE active = :_where_active;';

        $q = new Query('DELETE');
        $q->setTable('users');
        $q->addWhere('active', false);
        $q->buildQuery();

        $this->assertEquals($sql, $q->getSql());
        $this->assertEquals([
            ':_where_active' => 0,
        ], $q->getBinds());

        $q->addWhere('active', true);
        $q->buildQuery();

        $this->assertEquals([
            ':_where_active' => 1,
        ], $q->getBinds());

        $q->setConvertBoolToInt(false);
        $q->addWhere('active', false);
        $q->buildQuery();

        $this->assertEquals([
            ':_where_active' => false,
        ], $q->getBinds());

        $q->addWhere('active', true);
        $q->buildQuery();

        $this->assertEquals([
            ':_where_active' => true,
        ], $q->getBinds());
    }

}