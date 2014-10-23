<?php

class DatabaseConnectionIntegrationTest extends PHPUnit_Framework_TestCase
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

    public function testItReturnsCorrectValuesForUtilityFunctions()
    {
        $pdo = $this->connection->getPdo();
        $this->assertInstanceOf('PDO', $pdo);

        $driverName = $this->connection->getDriverName();
        $this->assertEquals('mysql', $driverName);

        $grammar = $this->connection->getQueryGrammar();
        $this->assertInstanceOf('Database\Query\Grammars\MySqlGrammar', $grammar);
    }

    public function testItPerformsTransactions()
    {
        $this->createTable();

        $this->connection->transaction(function($connection){
            $connection->query("INSERT INTO $this->tableName (name, value) VALUES (?,?)", array('joe', 1));
        });

        $rows = $this->connection->fetchAll("SELECT * FROM $this->tableName");

        $this->assertCount(1, $rows);
        $this->assertEquals(array('name' => 'joe', 'value' => 1), $rows[0]);

        try{
            $this->connection->transaction(function($connection){
                $connection->query("INSERT INTO $this->tableName (name, value) VALUES (?,?)", array('joseph', 2));

                throw new \Exception("rollback");
            });
        }catch (\Exception $e){}

        $rows = $this->connection->fetchAll("SELECT * FROM $this->tableName");

        $this->assertCount(1, $rows);

    }
}