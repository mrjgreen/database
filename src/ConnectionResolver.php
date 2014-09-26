<?php namespace Database;

use Database\Connectors\ConnectionFactory;

class ConnectionResolver implements ConnectionResolverInterface {

	/**
	 * All of the registered connection configs.
	 *
	 * @var array
	 */
	protected $connections = array();

    /**
     * All of the registered connections.
     *
     * @var array
     */
    protected $connectionCache = array();

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

	/**
	 * The default connection name.
	 *
	 * @var string
	 */
	protected $default;

	/**
	 * Create a new connection resolver instance.
	 *
	 * @param  array  $connections
	 * @return void
	 */
	public function __construct(array $connections = array(), ConnectionFactory $connectionFactory)
	{
        $this->connectionFactory = $connectionFactory;

		foreach ($connections as $name => $connection)
		{
			$this->addConnection($name, $connection);
		}
	}

	/**
	 * Get a database connection instance.
	 *
	 * @param  string  $name
	 * @return \Database\Connection
	 */
	public function connection($name = null)
	{
		if (is_null($name)) $name = $this->getDefaultConnection();

        if(!isset($this->connectionCache[$name]))
        {
            $this->connectionCache[$name] = $this->connectionFactory->make($this->connectionConfig($name));
        }

		return $this->connectionCache[$name];
	}

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return array
     */
    public function connectionConfig($name = null)
    {
        if (is_null($name)) $name = $this->getDefaultConnection();

        return $this->connections[$name];
    }

	/**
	 * Add a connection to the resolver.
     *
     * Can be an instance of \Database\Connection or a valid config array, if a connection factory has been set
	 *
	 * @param  string  $name
	 * @param  array  $connection
	 * @return void
	 */
	public function addConnection($name, $connection)
	{
        if(!is_array($connection))
        {
            throw new \InvalidArgumentException('Argument 2 must be an array containing a valid connection configuration. Type "' . gettype($connection) . '" given.');
        }

		$this->connections[$name] = $connection;
	}

	/**
	 * Check if a connection has been registered.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function hasConnection($name)
	{
		return isset($this->connections[$name]);
	}

	/**
	 * Get the default connection name.
	 *
	 * @return string
	 */
	public function getDefaultConnection()
	{
		return $this->default;
	}

	/**
	 * Set the default connection name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultConnection($name)
	{
		$this->default = $name;
	}
}
