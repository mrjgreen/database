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
            'test2' => array(
                'baz' => 'boo'
            )
        );

        $factory = m::mock('Illuminate\Database\Connectors\ConnectionFactory');

		$resolver = $this->getMock('Illuminate\Database\ConnectionResolver', null, array($configs, $factory));

		$this->assertTrue($resolver->hasConnection('test1'));
	}
}
