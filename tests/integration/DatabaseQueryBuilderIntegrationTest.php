<?php

class DatabaseQueryBuilderIntegrationTest extends AbstractDatabaseIntegrationTest
{
    public function testInsertUpdateDelete()
    {
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

    public function testOutfile()
    {
        $file = '/var/tmp/db_integration_test_' . uniqid();

        $res = $this->connection
            ->table($this->tableName)
            ->where('name', '=', 'joe')
            ->intoOutfile($file)
            ->query();

        $this->assertEquals(0, $res->rowCount());

        @unlink($file);
    }

    public function testOutfileWithTerminators()
    {
        $file = '/var/tmp/db_integration_test_' . uniqid();

        $res = $this->connection
            ->table($this->tableName)
            ->where('name', '=', 'joe')
            ->intoOutfile($file, function(\Database\Query\OutfileClause $outfile){
                $outfile
                    ->linesTerminatedBy("\n")
                    ->fieldsTerminatedBy("\t");
            })
            ->query();

        $this->assertEquals(0, $res->rowCount());

        @unlink($file);
    }
}