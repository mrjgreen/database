<?php namespace Database\Query;

use Traversable;

class InsertBuffer
{
    /**
     * The database connection instance.
     *
     * @var \Database\Query\Builder
     */
    protected $builder;

    /**
     * @var
     */
    protected $chunkSize;

    /**
     * @param Builder $builder
     * @param $chunkSize
     */
    public function __construct(Builder $builder, $chunkSize)
    {
        $this->builder = $builder;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return bool
     */
    public function insert(Traversable $values)
    {
        return $this->doInsert($values, 'insert');
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return bool
     */
    public function insertIgnore(Traversable $values)
    {
        return $this->doInsert($values, 'insertIgnore');
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return bool
     */
    public function replace(Traversable $values)
    {
        return $this->doInsert($values, 'replace');
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return bool
     */
    protected function doInsert(Traversable $values, $type)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        if (!is_array(reset($values))) {
            $values = array($values);
        }

        $sql = $this->grammar->{'compile' . ucfirst($type)}($this, $values);

        return $this->connection->query($sql, $this->buildBulkInsertBindings($values));
    }

    /**
     * Insert a new record into the database, with an update if it exists
     *
     * @param Traversable $values
     * @param array $updateValues an array of column => bindings pairs to update
     * @return \PDOStatement
     */
    public function insertUpdate(Traversable $values, array $updateValues)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        if (!is_array(reset($values))) {
            $values = array($values);
        }

        $bindings = $this->buildBulkInsertBindings($values);

        foreach($updateValues as $value)
        {
            if(!$value instanceof Expression) $bindings[] = $value;
        }

        $sql = $this->grammar->{'compileInsertOnDuplicateKeyUpdate'}($this, $values, $updateValues);

        return $this->connection->query($sql, $bindings);
    }

    /**
     * Alias for insertOnDuplicateKeyUpdate
     *
     * @param Traversable $values
     * @param array $updateValues
     * @return \PDOStatement
     */
    public function insertOnDuplicateKeyUpdate(Traversable $values, array $updateValues)
    {
        return $this->insertUpdate($values, $updateValues);
    }
}
