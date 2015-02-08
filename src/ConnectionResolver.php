<?php namespace Database;

use Database\Connectors\ConnectionFactory;
use Database\Connectors\ConnectionFactoryInterface;

class ConnectionResolver implements ConnectionResolverInterface
{

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
     * @param  array $connections
     * @param ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(array $connections = array(), ConnectionFactoryInterface $connectionFactory = null)
    {
        $this->connectionFactory = $connectionFactory ?: new ConnectionFactory();

        foreach ($connections as $name => $connection) {
            $this->addConnection($name, $connection);
        }
    }

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     * @return \Database\Connection
     */
    public function connection($name = null)
    {
        if (is_null($name)) $name = $this->getDefaultConnection();

        if (!isset($this->connectionCache[$name]))
        {
            $this->connectionCache[$name] = $this->newConnection($name);
        }

        return $this->connectionCache[$name];
    }

    /**
     * Get a new database connection, without the
     *
     * @param $name
     * @return mixed
     */
    public function newConnection($name = null)
    {
        if (is_null($name)) $name = $this->getDefaultConnection();

        return $this->connectionFactory->make($this->connectionConfig($name));
    }

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     * @return array
     */
    public function connectionConfig($name = null)
    {
        if (is_null($name)) $name = $this->getDefaultConnection();

        return $this->value($this->connections[$name]);
    }

    /**
     * Add a connection to the resolver.
     *
     * Can be an instance of \Database\Connection or a valid config array, if a connection factory has been set
     *
     * @param  string $name
     * @param  array $connection
     * @return void
     */
    public function addConnection($name, $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
     *
     * @param  string $name
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
     * @param  string $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
