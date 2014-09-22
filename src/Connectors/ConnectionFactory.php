<?php namespace Illuminate\Database\Connectors;

use PDO;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;

/**
 * Class ConnectionFactory
 * @package Illuminate\Database\Connectors
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
 *  )
 * </code>
 */
class ConnectionFactory {

	/**
	 * Establish a PDO connection based on the configuration.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection
	 */
	public function make(array $config, $name = null)
	{
		$config = $this->parseConfig($config, $name);

		if (isset($config['read']))
		{
			return $this->createReadWriteConnection($config);
		}

		return $this->createSingleConnection($config);
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array  $config
	 * @return \Illuminate\Database\Connection
	 */
	protected function createSingleConnection(array $config)
	{
		$pdo = $this->createConnector($config)->connect($config);

		return $this->createConnection($config['driver'], $pdo, $config['prefix']);
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array  $config
	 * @return \Illuminate\Database\Connection
	 */
	protected function createReadWriteConnection(array $config)
	{
		$connection = $this->createSingleConnection($this->getWriteConfig($config));

		return $connection->setReadPdo($this->createReadPdo($config));
	}

	/**
	 * Create a new PDO instance for reading.
	 *
	 * @param  array  $config
	 * @return \PDO
	 */
	protected function createReadPdo(array $config)
	{
		$readConfig = $this->getReadConfig($config);

		return $this->createConnector($readConfig)->connect($readConfig);
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array  $config
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
	 * @param  array  $config
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
	 * @param  array   $config
	 * @param  string  $type
	 * @return array
	 */
	protected function getReadWriteConfig(array $config, $type)
	{
		if (isset($config[$type][0]))
		{
			return $config[$type][array_rand($config[$type])];
		}

		return $config[$type];
	}

	/**
	 * Merge a configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @param  array  $merge
	 * @return array
	 */
	protected function mergeReadWriteConfig(array $config, array $merge)
	{
        return array_diff_key(array_merge($config, $merge), array_flip(array('read', 'write')));
	}

	/**
	 * Parse and prepare the database configuration.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return array
	 */
	protected function parseConfig(array $config, $name)
	{
        return $config + array(
            'prefix'    => '',
            'name'      => $name
        );
	}

	/**
	 * Create a connector instance based on the configuration.
	 *
	 * @param  array  $config
	 * @return \Illuminate\Database\Connectors\ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createConnector(array $config)
	{
		if ( ! isset($config['driver']))
		{
			throw new \InvalidArgumentException("A driver must be specified.");
		}

		switch ($config['driver'])
		{
			case 'mysql':
				return new MySqlConnector;

			case 'pgsql':
				return new PostgresConnector;

			case 'sqlite':
				return new SQLiteConnector;

			case 'sqlsrv':
				return new SqlServerConnector;
		}

		throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}]");
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string   $driver
	 * @param  \PDO     $connection
	 * @param  string   $prefix
	 * @return \Illuminate\Database\Connection
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection($driver, PDO $connection, $prefix = '')
	{
		switch ($driver)
		{
			case 'mysql':
                $queryGrammar = new MySqlGrammar();
                break;

			case 'pgsql':
                $queryGrammar = new PostgresGrammar();
                break;

			case 'sqlite':
                $queryGrammar = new SQLiteGrammar();
                break;

			case 'sqlsrv':
                $queryGrammar = new SqlServerGrammar();
                break;

            default:
                throw new \InvalidArgumentException("Unsupported driver [$driver]");
		}

        return new Connection($connection, $queryGrammar, $prefix);

	}

}
