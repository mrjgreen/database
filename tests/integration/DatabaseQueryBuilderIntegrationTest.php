<?php

class DatabaseQueryBuilderIntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Database\Connection
     */
    protected $connection;

    protected $tableName = 'test.database_integration_test';

    public function setUp()
    {
        $factory = new \Database\Connectors\ConnectionFactory();

        $this->connection = $factory->make(include __DIR__ . '/config.php');
    }

    public function createTable()
    {
        $this->connection->query("CREATE DATABASE IF NOT EXISTS test");

        $this->connection->query("CREATE TABLE IF NOT EXISTS $this->tableName (`name` varchar(255),`value` integer(8)) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->connection->query("TRUNCATE TABLE $this->tableName");
    }

    public function testInsertUpdateDelete()
    {
        $this->createTable();

        $statement = $this->connection->table($this->tableName)
            ->insert(array(
                'name'  => 'joe',
                'value' => 1
            ));

        $this->assertEquals(1, $statement->rowCount());

        $statement = $this->connection->table($this->tableName)
            ->where('name', '=', 'joe')
            ->update(array(
                'value' => 5
            ));

        $this->assertEquals(1, $statement->rowCount());

        $this->connection->table($this->tableName)
            ->where('name', '=', 'joe')
            ->increment('value');

        $rows = $this->connection->table($this->tableName)->get();

        $this->assertEquals(array(
            array('name' => 'joe', 'value' => 6)
        ), $rows);

        $statement = $this->connection->table($this->tableName)
            ->where('name', '=', 'joe')
            ->delete();

        $this->assertEquals(1, $statement->rowCount());

        $exists = $this->connection->table($this->tableName)
            ->where('name', '=', 'joe')
            ->exists();

        $this->assertFalse($exists);

    }
}