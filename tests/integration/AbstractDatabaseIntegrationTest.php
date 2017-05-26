<?php

abstract class AbstractDatabaseIntegrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Database\Connection
     */
    protected $connection;

    protected $tableName = 'test.database_integration_test';

    public function setUp()
    {
        $factory = new \Database\Connectors\ConnectionFactory();

        $configs = include __DIR__ . '/config.php';

        foreach ($configs as $config) {
            try {
                $this->connection = $factory->make($config);

                $this->createTable();

                return;
            } catch (\PDOException $e) {
            }
        }

        throw $e;
    }

    private function createTable()
    {
        $this->connection->query("CREATE DATABASE IF NOT EXISTS test");

        $this->connection->query("CREATE TABLE IF NOT EXISTS $this->tableName (`name` varchar(255),`value` integer(8)) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->connection->query("TRUNCATE TABLE $this->tableName");
    }
}
