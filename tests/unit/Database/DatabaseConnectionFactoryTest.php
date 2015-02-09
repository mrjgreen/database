<?php

use Mockery as m;

class DatabaseConnectionFactoryPDOStub extends PDO {
	public function __construct() {}
}

class DatabaseConnectionFactoryTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testMakeCallsCreateConnection()
	{
		$factory = $this->getMock('Database\Connectors\ConnectionFactory', array('createConnector', 'createConnection', 'createQueryGrammar', 'createExceptionHandler'));

		$config = array('driver' => 'mysql', 'prefix' => 'prefix', 'database' => 'database');

		$pdo = new DatabaseConnectionFactoryPDOStub;

        $connector = m::mock('stdClass');
		$connector->shouldReceive('connect')->once()->with($config)->andReturn($pdo);

		$mockGrammar = $this->getMock('Database\Query\Grammars\MysqlGrammar');
		$mockExceptionHandler = $this->getMock('Database\Exception\ExceptionHandlerInterface');

		$mockConnection = $this->getMockConnectionWithExpectations($pdo, $mockGrammar);

		$factory->expects($this->once())->method('createConnector')->with($config['driver'])->will($this->returnValue($connector));
		$factory->expects($this->once())->method('createQueryGrammar')->with('mysql')->will($this->returnValue($mockGrammar));
		$factory->expects($this->once())->method('createConnection')->will($this->returnValue($mockConnection));
		$factory->expects($this->once())->method('createExceptionHandler')->with($config)->will($this->returnValue($mockExceptionHandler));

        $connection = $factory->make($config);

		$this->assertSame($mockConnection, $connection);
	}


	public function testMakeCallsCreateConnectionForReadWrite()
	{
		$factory = $this->getMock('Database\Connectors\ConnectionFactory', array('createConnector', 'createConnection', 'createQueryGrammar'));
		$connector = m::mock('stdClass');
		$config = array(
			'read' => array('database' => 'database'),
			'write' => array('database' => 'database'),
			'driver' => 'mysql', 'prefix' => 'prefix', 'name' => 'foo'
		);
		$expect = $config;
		unset($expect['read']);
		unset($expect['write']);
		$expect['database'] = 'database';
		$pdo = new DatabaseConnectionFactoryPDOStub;
		$connector->shouldReceive('connect')->twice()->with($expect)->andReturn($pdo);

		$mockGrammar = $this->getMock('Database\Query\Grammars\MysqlGrammar');

        $mockConnection = $this->getMockConnectionWithExpectations($pdo, $mockGrammar);

        $factory->expects($this->exactly(2))->method('createConnector')->with($expect['driver'])->will($this->returnValue($connector));
		$factory->expects($this->once())->method('createQueryGrammar')->with('mysql')->will($this->returnValue($mockGrammar));
		$factory->expects($this->once())->method('createConnection')->will($this->returnValue($mockConnection));

		$connection = $factory->make($config, 'foo');

		$this->assertSame($mockConnection, $connection);
	}

	private function getMockConnectionWithExpectations($pdo, $grammar)
	{
		$mockConnection = $this->getMock('Database\Connection', array('setPdo','setReconnector', 'setQueryGrammar', 'setExceptionHandler'), array($pdo));
		$mockConnection->expects($this->once())->method('setReconnector')->will($this->returnSelf());
		$mockConnection->expects($this->once())->method('setQueryGrammar')->with($grammar)->will($this->returnSelf());
		$mockConnection->expects($this->once())->method('setExceptionHandler')->will($this->returnSelf());

		$mockConnection->expects($this->once())->method('setPdo')->with($pdo)->will($this->returnValue($mockConnection));

		return $mockConnection;
	}

	public function testProperInstancesAreReturnedForProperDrivers()
	{
		$factory = new Database\Connectors\ConnectionFactory();
		$this->assertInstanceOf('Database\Connectors\MySqlConnector', $factory->createConnector('mysql'));
		$this->assertInstanceOf('Database\Connectors\PostgresConnector', $factory->createConnector('pgsql'));
		$this->assertInstanceOf('Database\Connectors\SQLiteConnector', $factory->createConnector('sqlite'));
		$this->assertInstanceOf('Database\Connectors\SqlServerConnector', $factory->createConnector('sqlsrv'));
	}

    /**
     * @dataProvider driversGrammarProvider
     */
	public function testProperGrammarInstancesAreReturnedForProperDrivers($driver, $instance)
	{
		$factory = $this->getMock('Database\Connectors\ConnectionFactory', array('createConnector'), array());

        if(is_null($instance))
        {
            $this->setExpectedException('InvalidArgumentException');
        }
		else
		{
			$mock = m::mock('stdClass');
			$mock->shouldReceive('connect')->andReturn(m::mock('PDO'));

			$factory->expects($this->once())->method('createConnector')->willReturn($mock);
		}

        $connection = $factory->make(array(
            'driver' => $driver
        ));

		if(!is_null($instance))
        {
            $this->assertInstanceOf($instance, $connection->getQueryGrammar());
        }
	}

    public function driversGrammarProvider()
    {
        return array(
            array('mysql', 'Database\Query\Grammars\MySqlGrammar'),
            array('pgsql', 'Database\Query\Grammars\PostgresGrammar'),
            array('sqlite', 'Database\Query\Grammars\SQLiteGrammar'),
            array('sqlsrv', 'Database\Query\Grammars\SqlServerGrammar'),
            //array('blahblah', null)
        );
    }


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testIfDriverIsntSetExceptionIsThrown()
	{
		$factory = new Database\Connectors\ConnectionFactory();
		$factory->make(array('foo'));
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsThrownOnUnsupportedDriver()
	{
		$factory = new Database\Connectors\ConnectionFactory();
		$factory->make(array('driver' => 'foo'));
	}

}
