<?php

class DatabaseConnectionIntegrationTest extends AbstractDatabaseIntegrationTest
{
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