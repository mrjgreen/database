<?php namespace Database\Connectors;

use Database\Connection;
use Database\Exception\ExceptionHandler;
use Database\Query\Grammars\MySqlGrammar;
use Database\Query\Grammars\PostgresGrammar;
use Database\Query\Grammars\SqlServerGrammar;
use Database\Query\Grammars\SQLiteGrammar;
use Database\QueryLogger;
use Psr\Log\LoggerInterface;

/**
 * Class ConnectionFactory
 * @package Database\Connectors
 *
 * Build a connection from a config with a format like:
 *
 *
 * <code>
 *  array(
 *      'read' => array(
 *          'host' => '192.168.1.1',
 *      ),
 *      'write' => array(
 *          'host' => '196.168.1.2'
 *      ),
 *      'driver'    => 'mysql',
 *      'database'  => 'database',
 *      'username'  => 'root',
 *      'password'  => '',
 *      'charset'   => 'utf8',
 *      'collation' => 'utf8_unicode_ci',
 *      'prefix'    => '',
 *      'lazy'      => true/false
 *  )
 * </code>
 */
class ConnectionFactory implements ConnectionFactoryInterface
{

    /**
     * @var null|string
     */
    protected $connectionClassName = 'Database\Connection';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $excludedLogParams = array('password');

    public function __construct($connectionClassName = null, LoggerInterface $logger = null)
    {
        if ($connectionClassName) {
            $this->connectionClassName = $connectionClassName;
        }

        $this->logger = $logger ?: new QueryLogger();
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param  array $config
     * @return \Database\Connection
     */
    public function make(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException("A driver must be specified.");
        }

        return $this->makeConnection($config, !empty($config['lazy']))->setReconnector(function (Connection $connection) use ($config) {
            $fresh = $this->makeConnection($config, false);

            return $connection->setPdo($fresh->getPdo())->setReadPdo($fresh->getReadPdo());
        });
    }

    /**
     * Establish a PDO connection based on the configuration, return wrapped in a Connection instance.
     *
     * @param array $config
     * @param bool $lazy
     * @return \Database\Connection
     */
    protected function makeConnection(array $config, $lazy)
    {
        if (isset($config['read'])) {
            return $this->createReadWriteConnection($config, $lazy);
        }

        return $this->createSingleConnection($config, $lazy);
    }

    /**
     * Create a single database connection instance.
     *
     * @param  array $config
     * @param  bool $lazy
     * @return \Database\Connection
     */
    protected function createSingleConnection(array $config, $lazy)
    {
        $connection = $this->createConnection();

        $connection
            ->setExceptionHandler($this->createExceptionHandler($config))
            ->setQueryGrammar($this->createQueryGrammar($config['driver']))
            ->setTablePrefix(isset($config['prefix']) ? $config['prefix'] : '')
            ->setLogger($this->logger);

        if(!$lazy)
        {
            $connection->setPdo($this->createConnector($config['driver'])->connect($config));
        }

        return $connection;
    }


    /**
     * @return \Database\Connection
     */
    protected function createConnection()
    {
        return new $this->connectionClassName;
    }

    /**
     * Create a single database connection instance.
     *
     * @param  array $config
     * @return \Database\Connection
     */
    protected function createReadWriteConnection(array $config, $lazy)
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config), $lazy);

        if(!$lazy)
        {
            $connection->setReadPdo($this->createReadPdo($config));
        }

        return $connection;
    }

    /**
     * Create a new PDO instance for reading.
     *
     * @param  array $config
     * @return \PDO
     */
    protected function createReadPdo(array $config)
    {
        $readConfig = $this->getReadConfig($config);

        return $this->createConnector($readConfig['driver'])->connect($readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param  array $config
     * @return array
     */
    protected function getReadConfig(array $config)
    {
        $readConfig = $this->getReadWriteConfig($config, 'read');

        return $this->mergeReadWriteConfig($config, $readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param  array $config
     * @return array
     */
    protected function getWriteConfig(array $config)
    {
        $writeConfig = $this->getReadWriteConfig($config, 'write');

        return $this->mergeReadWriteConfig($config, $writeConfig);
    }

    /**
     * Get a read / write level configuration.
     *
     * @param  array $config
     * @param  string $type
     * @return array
     */
    protected function getReadWriteConfig(array $config, $type)
    {
        if (isset($config[$type][0])) {
            return $config[$type][array_rand($config[$type])];
        }

        return $config[$type];
    }

    /**
     * Merge a configuration for a read / write connection.
     *
     * @param  array $config
     * @param  array $merge
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge)
    {
        return array_diff_key(array_merge($config, $merge), array_flip(array('read', 'write')));
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param  string $driver
     * @return \Database\Connectors\ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createConnector($driver)
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlConnector;

            case 'pgsql':
                return new PostgresConnector;

            case 'sqlite':
                return new SQLiteConnector;

            case 'sqlsrv':
                return new SqlServerConnector;
        }

        throw new \InvalidArgumentException("Unsupported driver [$driver]");
    }

    /**
     * Create a new connection instance.
     *
     * @param $driver
     * @return MySqlGrammar|PostgresGrammar|SQLiteGrammar|SqlServerGrammar
     *
     * @throws \InvalidArgumentException
     */
    protected function createQueryGrammar($driver)
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlGrammar();
                break;

            case 'pgsql':
                return new PostgresGrammar();
                break;

            case 'sqlite':
                return new SQLiteGrammar();
                break;

            case 'sqlsrv':
                return new SqlServerGrammar();
                break;
        }

        throw new \InvalidArgumentException("Unsupported driver [$driver]");
    }

    protected function createExceptionHandler(array $config)
    {
        $logSafeParams = array_diff_key($config, array_flip($this->excludedLogParams));

        return new ExceptionHandler($logSafeParams);
    }
}
