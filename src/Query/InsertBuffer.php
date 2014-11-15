<?php namespace Database\Query;

use Traversable;
use Closure;

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
     * @return int
     */
    public function insert(Traversable $values)
    {
        return $this->doInsert($values, 'insert');
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return int
     */
    public function insertIgnore(Traversable $values)
    {
        return $this->doInsert($values, 'insertIgnore');
    }

    /**
     * Insert a new record into the database.
     *
     * @param  Traversable $values
     * @return int
     */
    public function replace(Traversable $values)
    {
        return $this->doInsert($values, 'replace');
    }

    /**
     * Insert a new record into the database.
     *
     * @param Traversable $values
     * @param $type
     * @return int
     */
    protected function doInsert(Traversable $values, $type)
    {
        $inserts = 0;

        $this->buffer($values, function(array $buffer) use($type, &$inserts){
            $inserts += $this->builder->doInsert($buffer, $type)->rowCount();
        });

        return $inserts;
    }

    /**
     * Insert a new record into the database, with an update if it exists
     *
     * @param Traversable $values
     * @param array $updateValues an array of column => bindings pairs to update
     * @return int
     */
    public function insertUpdate(Traversable $values, array $updateValues)
    {
        $upserts = 0;

        $this->buffer($values, function(array $buffer) use($updateValues, &$upserts){
            $upserts += $this->builder->insertUpdate($buffer, $updateValues)->rowCount();
        });

        return $upserts;
    }

    /**
     * Alias for insertOnDuplicateKeyUpdate
     *
     * @param Traversable $values
     * @param array $updateValues
     * @return int
     */
    public function insertOnDuplicateKeyUpdate(Traversable $values, array $updateValues)
    {
        return $this->insertUpdate($values, $updateValues);
    }

    /**
     * Loop through a traversable collection and call a closure after every X elements have been buffered
     *
     * @param Traversable $values
     * @param callable $callback
     */
    private function buffer(Traversable $values, Closure $callback)
    {
        // Keeping count the number of items is an order of magnitude quicker than calling count($buffer)
        $size = 0;
        $buffer = array();

        foreach($values as $row)
        {
            $buffer[] = $row;

            if(++$size >= $this->chunkSize)
            {
                $callback($buffer);

                $buffer = array();
                $size = 0;
            }
        }

        // Insert the remainder
        if($size)
        {
            $callback($buffer);
        }
    }
}
