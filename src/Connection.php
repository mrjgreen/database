<?php namespace Database;

use Database\Exception\ExceptionHandler;
use Database\Exception\ExceptionHandlerInterface;
use Database\Query\Grammars\Grammar;
use PDO;
use Closure;
use DateTime;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Connection implements ConnectionInterface
{
    use LoggerAwareTrait;

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
     * @var \Database\Exception\ExceptionHandlerInterface
     */
    protected $exceptionHandler;

    /**
     * Create a new database connection instance.
     *
     * @param PDO $pdo
     * @param Grammar $queryGrammar
     * @param ExceptionHandlerInterface $exceptionHandler
     * @param string $tablePrefix
     */
    public function __construct(PDO $pdo = null, Grammar $queryGrammar = null, ExceptionHandlerInterface $exceptionHandler = null, $tablePrefix = '')
    {
        $this->pdo = $pdo;

        $this->queryGrammar = $queryGrammar ?: new Grammar();

        $this->exceptionHandler = $exceptionHandler ?: new ExceptionHandler();

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
     * @return \PDOStatement
     */
    public function insert($table, array $values)
    {
        return $this->table($table)->insert($values);
    }

    /**
     * @param $table
     * @param $values
     * @return \PDOStatement
     */
    public function insertIgnore($table, array $values)
    {
        return $this->table($table)->insertIgnore($values);
    }

    /**
     * @param $table
     * @param $values
     * @return \PDOStatement
     */
    public function replace($table, array $values)
    {
        return $this->table($table)->replace($values);
    }

    /**
     * @param $table
     * @param $values
     * @param $updateValues
     * @return \PDOStatement
     */
    public function insertUpdate($table, array $values, array $updateValues)
    {
        return $this->table($table)->insertUpdate($values, $updateValues);
    }

    /**
     * @param $table
     * @param $where
     * @param $bindings
     * @return \PDOStatement
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
     * @return \PDOStatement
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
     * @throws \Exception
     */
    protected function run($query, $bindings, $useReadPdo = false)
    {
        $this->reconnectIfMissingConnection();

        // We can calculate the time it takes to execute the query and log the SQL, bindings and time against our logger.
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
     * @throws \Exception
     */
    private function execute($query, $bindings, $useReadPdo)
    {
        if ($this->pretending()) return new \PDOStatement();

        $pdo = $useReadPdo ? $this->getReadPdo() : $this->getPdo();

        try {
            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $pdo->prepare($query);

            $statement->execute($this->prepareBindings($bindings));
        }
            // If an exception occurs when attempting to run a query, we'll call the exception handler
            // if there is one, or throw the exception if not
        catch (\Exception $e) {

            if($this->exceptionHandler)
            {
                $this->exceptionHandler->handle($query, $this->prepareBindings($bindings), $e);
            }

            throw $e;
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
     * @return $this
     */
    public function beginTransaction()
    {
        $this->reconnectIfMissingConnection();
        
        $this->pdo->beginTransaction();

        return $this;
    }

    /**
     * Commit the active database transaction.
     *
     * @return $this
     */
    public function commit()
    {
        $this->pdo->commit();

        return $this;
    }

    /**
     * @return $this
     */
    public function rollBack()
    {
        $this->pdo->rollBack();

        return $this;
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Disconnect from the underlying PDO connection.
     *
     * @return $this
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);

        return $this;
    }

    /**
     *
     */
    public function connect()
    {
        $this->reconnectIfMissingConnection();

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

            try
            {
                return call_user_func($this->reconnector, $this);
            }
            catch(\PDOException $e)
            {
                $this->exceptionHandler->handle("Connection attempt", array(), $e);
            }
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
        if (!$this->loggingQueries || !$this->logger) return;

        $time = $start ? round((microtime(true) - $start) * 1000, 2) : null;

        $this->logger->debug($query, array(
            'bindings' => $bindings,
            'time' => $time
        ));
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
        if (!$this->readPdo || $this->pdo->inTransaction())
        {
            return $this->getPdo();
        }

        return $this->readPdo;
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
     * @return $this
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
     * Enable the query log on the connection.
     *
     * @return $this
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;

        if(!$this->logger)
        {
            $this->logger = new QueryLogger();
        }

        return $this;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return $this
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
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);

        return $this;
    }

    /**
     * Get the logger.
     *
     * @return LoggerInterface $logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param ExceptionHandlerInterface $exceptionHandler
     * @return $this
     */
    public function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;

        return $this;
    }
}
