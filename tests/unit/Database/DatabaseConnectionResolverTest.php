<?php

use Mockery as m;

class DatabaseConnectionResolverTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testConnectionsCanBeAddedAtConstruction()
	{
        $configs = array(
            'test1' => array(
                'foo' => 'bar'
            ),
            'test2' => m::mock('Database\Connection')
        );

        $factory = m::mock('Database\Connectors\ConnectionFactory');

        $connectionMock = m::mock('stdClass');

        $factory->shouldReceive('make')->once()->with($configs['test1'])->andReturn($connectionMock);

		$resolver = $this->getMock('Database\ConnectionResolver', null, array($configs, $factory));

		$this->assertTrue($resolver->hasConnection('test1'));

        $connection = $resolver->connection('test1');

        $this->assertSame($connectionMock, $connection);

        $factory->shouldReceive('make')->never();

        $connection = $resolver->connection('test2');

        $this->assertSame($configs['test2'], $connection);
	}

    public function testItReturnsADefaultConnection()
    {
        $configs = array(
            'test' => m::mock('Database\Connection')
        );

        $resolver = $this->getMock('Database\ConnectionResolver', null, array($configs));

        $resolver->setDefaultConnection('test');

        $connection = $resolver->connection();

        $this->assertSame($configs['test'], $connection);
    }
}
