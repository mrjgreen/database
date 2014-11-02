<?php namespace Database;

use Database\Query\Grammars\Grammar;
use PDO;
use Closure;
use DateTime;

class Connection implements ConnectionInterface
{

    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var PDO
     */
    protected $readPdo;

    /**
     * The reconnector instance for the connection.
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * The query grammar implementation.
     *
     * @var \Database\Query\Grammars\Grammar
     */
    protected $queryGrammar;

    /**
     * The default fetch mode of the connection.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected $queryLog = array();

    /**
     * Indicates whether queries are being logged.
     *
     * @var bool
     */
    protected $loggingQueries = false;

    /**
     * Indicates if the connection is in a "dry run".
     *
     * @var bool
     */
    protected $pretending = false;


    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO $pdo
     * @param  string $tablePrefix
     * @return void
     */
    public function __construct(PDO $pdo, Grammar $queryGrammar = null, $tablePrefix = '')
    {
        $this->pdo = $pdo;

        $this->queryGrammar = $queryGrammar ?: new Grammar();

        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  string $table
     * @return \Database\Query\Builder
     */
    public function table($table)
    {
        $query = new Query\Builder($this, $this->getQueryGrammar());

        return $query->from($table);
    }

    /**
     * @param $table
     * @param $values
     * @return bool
     */
    public function insert($table, $values)
    {
        return $this->table($table)->insert($values);
    }

    /**
     * @param $table
     * @param $values
     * @return bool
     */
    public function insertIgnore($table, $values)
    {
        return $this->table($table)->insertIgnore($values);
    }

    /**
     * @param $table
     * @param $values
     * @return bool
     */
    public function replace($table, $values)
    {
        return $this->table($table)->replace($values);
    }

    /**
     * @param $table
     * @param $values
     * @param $updateValues
     * @return bool
     */
    public function insertUpdate($table, $values, array $updateValues)
    {
        return $this->table($table)->insertUpdate($values, $updateValues);
    }

    /**
     * @param $table
     * @param $where
     * @param $bindings
     * @return bool
     */
    public function delete($table, $where, array $bindings = array())
    {
        return $this->table($table)->whereRaw($where, $bindings)->delete();
    }

    /**
     * @param $table
     * @param $values
     * @param $where
     * @param $bindings
     * @return bool
     */
    public function update($table, $values, $where, array $bindings = array())
    {
        return $this->table($table)->whereRaw($where, $bindings)->update($values);
    }

    /**
     * Get a new raw query expression.
     *
     * @param  mixed $value
     * @return \Database\Query\Expression
     */
    public function raw($value)
    {
        return new Query\Expression($value);
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return mixed
     */
    public function fetchOne($query, array $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, $useReadPdo)->fetchColumn();
    }

    /**
     * Run a select statement against the database, and return the first row based on the current fetch mode
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return array
     */
    public function fetch($query, array $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, $useReadPdo)->fetch($this->getFetchMode());
    }

    /**
     * Run a select statement against the database, and return the first row as a numeric array
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return array
     */
    public function fetchNumeric($query, array $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, $useReadPdo)->fetch(PDO::FETCH_NUM);
    }

    /**
     * Run a select statement against the database, and return an array containing all rows based on the current fetch mode
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return array
     */
    public function fetchAll($query, array $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, $useReadPdo)->fetchAll($this->getFetchMode());
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return \PDOStatement
     */
    public function query($query, array $bindings = array())
    {
        return $this->run($query, $bindings);
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        $this->pretending = true;

        $this->queryLog = array();

        // Basically to make the database connection "pretend", we will just return
        // the default values for all the query methods, then we will return an
        // array of queries that were "executed" within the Closure callback.
        $callback($this);

        $this->pretending = false;

        return $this;
    }

    /**
     * @param null $name
     * @return mixed|string
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Escape a value ready to be inserted into the database
     *
     * @param $string
     * @return array|string
     */
    public function quote($string)
    {
        if (is_array($string)) {
            return array_map(array($this->pdo, 'quote'), $string);
        }

        return $this->pdo->quote($string);
    }

    /**
     * Escape a value or array of values and bind them into an sql statement
     *
     * @param $sql
     * @param array $bind
     * @return mixed
     */
    public function quoteInto($sql, array $bind = array())
    {
        foreach ($bind as $key => $value) {
            $replace = (is_numeric($key) ? '?' : ':' . $key);

            $sql = substr_replace($sql, $this->quote($value), strpos($sql, $replace), strlen($replace));
        }

        return $sql;
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return \PDOStatement
     *
     * @throws \Database\QueryException
     */
    protected function run($query, $bindings, $useReadPdo = false)
    {
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        $statement = $this->execute($query, $bindings, $useReadPdo);

        $this->logQuery($query, $bindings, $start);

        return $statement;
    }

    /**
     * @param $query
     * @param $bindings
     * @param $useReadPdo
     * @return \PDOStatement
     */
    private function execute($query, $bindings, $useReadPdo)
    {
        if ($this->pretending()) return new \PDOStatement();

        $pdo = $useReadPdo ? $this->getReadPdo() : $this->getPdo();

        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $pdo->prepare($query);

            $statement->execute($this->prepareBindings($bindings));
        }
            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
        catch (\Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $statement;
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of the DateTime class into an actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTime) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif ($value === false) {
                $bindings[$key] = 0;
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(Closure $callback)
    {
        $this->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback($this);

            $this->commit();
        }

            // If we catch an exception, we will roll back so nothing gets messed
            // up in the database. Then we'll re-throw the exception so it can
            // be handled how the developer sees fit for their applications.
        catch (\Exception $e) {
            $this->rollBack();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        ++$this->transactions;

        if ($this->transactions == 1) {
            $this->pdo->beginTransaction();
        }

        return $this;
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) $this->pdo->commit();

        --$this->transactions;

        return $this;
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;

            $this->pdo->rollBack();
        } else {
            --$this->transactions;
        }

        return $this;
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    /**
     * Disconnect from the underlying PDO connection.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);

        return $this;
    }

    /**
     * Reconnect to the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }

        throw new \LogicException("Lost connection and no reconnector available.");
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->getPdo()) || is_null($this->getReadPdo())) {
            $this->reconnect();
        }
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  float $start
     * @return void
     */
    protected function logQuery($query, $bindings, $start = null)
    {
        if (!$this->loggingQueries) return;

        $time = $start ? round((microtime(true) - $start) * 1000, 2) : null;

        $this->queryLog[] = compact('query', 'bindings', 'time');
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Get the current PDO connection used for reading.
     *
     * @return \PDO
     */
    public function getReadPdo()
    {
        if ($this->transactions >= 1) return $this->getPdo();

        return $this->readPdo ?: $this->pdo;
    }

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|null $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Set the PDO connection used for reading.
     *
     * @param  \PDO|null $pdo
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * Set the reconnect instance on the connection.
     *
     * @param  callable $reconnector
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \Database\Query\Grammars\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Set the query grammar used by the connection.
     *
     * @param  \Database\Query\Grammars\Grammar
     * @return void
     */
    public function setQueryGrammar(Query\Grammars\Grammar $grammar)
    {
        $this->queryGrammar = $grammar;

        return $this;
    }

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * Get the default fetch mode for the connection.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Set the default fetch mode for the connection.
     *
     * @param  int $fetchMode
     * @return int
     */
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = array();

        return $this;
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;

        return $this;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;

        return $this;
    }

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string $prefix
     * @return void
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);

        return $this;
    }
}
