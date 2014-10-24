<?php

use Mockery as m;

class DatabaseConnectionTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testFetchOneCallsSelectAndReturnsSingleResult()
	{
		$connection = $this->getMockConnection(array('run'));

		$statement = $this->getMock('PDOStatement', array('fetchColumn'));
		$connection->expects($this->once())->method('run')->with('foo', array('bar' => 'baz'))->will($this->returnValue($statement));
		$statement->expects($this->once())->method('fetchColumn')->will($this->returnValue('boom'));

		$result = $connection->fetchOne('foo', array('bar' => 'baz'));
		$this->assertEquals('boom', $result);
	}


	public function testFetchProperlyCallsPDO()
	{
		$pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('prepare'));
		$writePdo = $this->getMock('DatabaseConnectionTestMockPDO', array('prepare'));
		$writePdo->expects($this->never())->method('prepare');
		$statement = $this->getMock('PDOStatement', array('execute', 'fetch'));
		$statement->expects($this->once())->method('execute')->with($this->equalTo(array('foo' => 'bar')));
		$statement->expects($this->once())->method('fetch')->will($this->returnValue(array('boom')));
		$pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
		$mock = $this->getMockConnection(array('prepareBindings', 'query'), $writePdo);
        $mock->enableQueryLog();
		$mock->setReadPdo($pdo);
		$mock->setPdo($pdo);
		$mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(array('foo' => 'bar')))->will($this->returnValue(array('foo' => 'bar')));
		$results = $mock->fetch('foo', array('foo' => 'bar'));
		$this->assertEquals(array('boom'), $results);
		$log = $mock->getQueryLog();
		$this->assertEquals('foo', $log[0]['query']);
		$this->assertEquals(array('foo' => 'bar'), $log[0]['bindings']);
		$this->assertTrue(is_numeric($log[0]['time']));
	}

	public function testQueryCallsTheRunMethod()
	{
		$connection = $this->getMockConnection(array('run'));
		$connection->expects($this->once())->method('run')->with($this->equalTo('foo'), $this->equalTo(array('bar')))->will($this->returnValue('baz'));
		$results = $connection->query('foo', array('bar'));
		$this->assertEquals('baz', $results);
	}

	public function testQueryProperlyCallsPDO()
	{
		$pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('prepare'));
		$statement = $this->getMock('PDOStatement', array('execute'));
		$statement->expects($this->once())->method('execute')->with($this->equalTo(array('bar')))->will($this->returnValue('foo'));
		$pdo->expects($this->once())->method('prepare')->with($this->equalTo('foo'))->will($this->returnValue($statement));
		$mock = $this->getMockConnection(array('prepareBindings'), $pdo);
        $mock->enableQueryLog();
		$mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(array('bar')))->will($this->returnValue(array('bar')));
		$results = $mock->query('foo', array('bar'));
		$this->assertInstanceOf('PDOStatement', $results);
		$log = $mock->getQueryLog();
		$this->assertEquals('foo', $log[0]['query']);
		$this->assertEquals(array('bar'), $log[0]['bindings']);
		$this->assertTrue(is_numeric($log[0]['time']));
	}


	public function testTransactionMethodRunsSuccessfully()
	{
		$pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('beginTransaction', 'commit'));
		$mock = $this->getMockConnection(array(), $pdo);
		$pdo->expects($this->once())->method('beginTransaction');
		$pdo->expects($this->once())->method('commit');
		$result = $mock->transaction(function($db) { return $db; });
		$this->assertEquals($mock, $result);
	}


	public function testTransactionMethodRollsbackAndThrows()
	{
		$pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('beginTransaction', 'commit', 'rollBack'));
		$mock = $this->getMockConnection(array(), $pdo);
		$pdo->expects($this->once())->method('beginTransaction');
		$pdo->expects($this->once())->method('rollBack');
		$pdo->expects($this->never())->method('commit');
		try
		{
			$mock->transaction(function() { throw new Exception('foo'); });
		}
		catch (Exception $e)
		{
			$this->assertEquals('foo', $e->getMessage());
		}
	}


	public function testFromCreatesNewQueryBuilder()
	{
		$conn = $this->getMockConnection();
		$builder = $conn->table('users');
		$this->assertInstanceOf('Database\Query\Builder', $builder);
		$this->assertEquals('users', $builder->from);
	}


	public function testPrepareBindings()
	{
		$date = m::mock('DateTime');
		$date->shouldReceive('format')->once()->with('foo')->andReturn('bar');
		$bindings = array('test' => $date);
		$conn = $this->getMockConnection();
		$grammar = m::mock('Database\Query\Grammars\Grammar');
		$grammar->shouldReceive('getDateFormat')->once()->andReturn('foo');
		$conn->setQueryGrammar($grammar);
		$result = $conn->prepareBindings($bindings);
		$this->assertEquals(array('test' => 'bar'), $result);
	}


	public function testPretendOnlyLogsQueries()
	{
		$connection = $this->getMockConnection();
        $connection->enableQueryLog();
		$queries = $connection->pretend(function($connection)
		{
			$connection->fetchAll('foo bar', array('baz'));
		})->getQueryLog();
		$this->assertEquals('foo bar', $queries[0]['query']);
		$this->assertEquals(array('baz'), $queries[0]['bindings']);
	}

    public function testItProxiesInsertToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('insert');
    }

    public function testItProxiesInsertIgnoreToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('insertIgnore');
    }

    public function testItProxiesReplaceToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('replace');
    }

    public function testItProxiesDeleteToBuilder()
    {
        $mock = m::mock('stdClass');
        $mock->shouldReceive('delete')->andReturn('baz');

        $mock2 = m::mock('stdClass');
        $mock2->shouldReceive('whereRaw')->with('foo', array('bar'))->andReturn($mock);

        $connection = $this->getMockConnection(array('table'));

        $connection->expects($this->once())
            ->method('table')
            ->with('testTable')
            ->willReturn($mock2);

        $connection->delete('testTable', 'foo', array('bar'));
    }

    public function testItProxiesUpdateToBuilder()
    {
        $mock = m::mock('stdClass');
        $mock->shouldReceive('update')->with(array('fuzz' => 'buzz'))->andReturn('baz');

        $mock2 = m::mock('stdClass');
        $mock2->shouldReceive('whereRaw')->with('foo', array('bar'))->andReturn($mock);

        $connection = $this->getMockConnection(array('table'));

        $connection->expects($this->once())
            ->method('table')
            ->with('testTable')
            ->willReturn($mock2);

        $connection->update('testTable', array('fuzz' => 'buzz'), 'foo', array('bar'));
    }

    public function testItProxiesInsertUpdateToBuilder()
    {
        $mock = m::mock('stdClass');
        $mock->shouldReceive('insertUpdate')->with(array('foo'), array('bar'))->andReturn('baz');

        $connection = $this->getMockConnection(array('table'));

        $connection->expects($this->once())
            ->method('table')
            ->with('testTable')
            ->willReturn($mock);

        $this->assertEquals('baz',$connection->insertUpdate('testTable', array('foo'), array('bar')));
    }

    private function doInsertTypeProxyCallsToBuilder($type)
    {
        $mock = m::mock('stdClass');
        $mock->shouldReceive($type)->with(array('foo'))->andReturn('baz');

        $connection = $this->getMockConnection(array('table'));

        $connection->expects($this->once())
            ->method('table')
            ->with('testTable')
            ->willReturn($mock);

        $this->assertEquals('baz',$connection->{$type}('testTable', array('foo')));
    }

    public function testQuoteInto()
    {
        $connection = $this->getMockConnection(array('quote'));

        $connection->expects($this->at(0))->method('quote')->with('foo')->willReturn('`foo`');
        $connection->expects($this->at(1))->method('quote')->with('bar')->willReturn('`bar`');

        $string = $connection->quoteInto('col1 = ? AND col2 = ?', array('foo', 'bar'));

        $this->assertEquals('col1 = `foo` AND col2 = `bar`', $string);
    }

    public function testQuote()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('quote'));
        $connection = $this->getMockConnection(array(), $pdo);

        $pdo->expects($this->once())->method('quote')->with('foo')->willReturn('`foo`');

        $string = $connection->quote('foo');

        $this->assertEquals('`foo`', $string);
    }

    public function testQuoteArray()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', array('quote'));
        $connection = $this->getMockConnection(array(), $pdo);

        $pdo->expects($this->at(0))->method('quote')->with('foo')->willReturn('`foo`');
        $pdo->expects($this->at(1))->method('quote')->with('bar')->willReturn('`bar`');

        $array = $connection->quote(array('foo', 'bar'));

        $this->assertEquals(array('`foo`', '`bar`'), $array);
    }

    public function testSetAndGetPrefix()
    {
        $connection = $this->getMockConnection(array());

        $connection->setTablePrefix('foo');
        $this->assertEquals('foo', $connection->getTablePrefix());

        $connection->setTablePrefix('bar');
        $this->assertEquals('bar', $connection->getTablePrefix());
    }

    public function testItCorrectlyEnablesAndDisablesLogging()
    {
        $connection = $this->getMockConnection(array());

        $connection->enableQueryLog();
        $this->assertEquals(true, $connection->logging());

        $connection->disableQueryLog();
        $this->assertEquals(false, $connection->logging());
    }

    public function testItCorrectlySetsTheFetchMode()
    {
        $connection = $this->getMockConnection(array());

        $connection->setFetchMode(10);
        $this->assertEquals(10, $connection->getFetchMode());

        $connection->setFetchMode(2);
        $this->assertEquals(2, $connection->getFetchMode());
    }

    /**
     * @param array $methods
     * @param null $pdo
     * @return Database\Connection
     */
	protected function getMockConnection($methods = array(), $pdo = null)
	{
		$pdo = $pdo ?: new DatabaseConnectionTestMockPDO;
        return $this->getMock('Database\Connection', $methods ?: null, array($pdo));
	}
}

class DatabaseConnectionTestMockPDO extends PDO { public function __construct() {} }
