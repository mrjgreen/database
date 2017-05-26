<?php

use Mockery as m;

class DatabaseConnectionTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        m::close();
    }


    public function testFetchOneCallsSelectAndReturnsSingleResult()
    {
        $connection = $this->getMockConnection(array('run'));

        $statement = $this->createMock('PDOStatement', array('fetchColumn'));
        $connection->expects($this->once())->method('run')->with('foo', array('bar' => 'baz'))->will($this->returnValue($statement));
        $statement->expects($this->once())->method('fetchColumn')->will($this->returnValue('boom'));

        $result = $connection->fetchOne('foo', array('bar' => 'baz'));
        $this->assertEquals('boom', $result);
    }


    public function testFetchProperlyCallsPDO()
    {
        $pdo = $this->getMockBuilder('DatabaseConnectionTestMockPDO')->setMethods(array('prepare'))->getMock();
        $writePdo = $this->getMockBuilder('DatabaseConnectionTestMockPDO')->setMethods(array('prepare', 'inTransaction'))->getMock();
        $writePdo->expects($this->never())->method('prepare');
        $writePdo->expects($this->exactly(2))->method('inTransaction')->willReturn(false);
        $statement = $this->getMockBuilder('PDOStatement')->setMethods(array('execute', 'fetch'))->getMock();
        $statement->expects($this->once())->method('execute')->with($this->equalTo(array('foo' => 'bar')));
        $statement->expects($this->once())->method('fetch')->will($this->returnValue(array('boom')));
        $pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
        $mock = $this->getMockConnection(array('prepareBindings', 'query'), $writePdo);
        $mock->setReadPdo($pdo);
        $mock->setPdo($writePdo);
        $mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(array('foo' => 'bar')))->will($this->returnValue(array('foo' => 'bar')));
        $results = $mock->fetch('foo', array('foo' => 'bar'));
        $this->assertEquals(array('boom'), $results);
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
        $pdo = $this->createMock('DatabaseConnectionTestMockPDO');
        $statement = $this->createMock('PDOStatement');
        $statement->expects($this->once())->method('execute')->with($this->equalTo(array('bar')))->will($this->returnValue('foo'));
        $pdo->expects($this->once())->method('prepare')->with($this->equalTo('foo'))->will($this->returnValue($statement));
        $mock = $this->getMockConnection(array('prepareBindings'), $pdo);
        $mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(array('bar')))->will($this->returnValue(array('bar')));
        $results = $mock->query('foo', array('bar'));
        $this->assertInstanceOf('PDOStatement', $results);
    }


    public function testTransactionMethodRunsSuccessfully()
    {
        $pdo = $this->getMockBuilder('DatabaseConnectionTestMockPDO')->setMethods(array('beginTransaction', 'commit'))->getMock();
        $connection = new \Database\Connection($pdo);
        $pdo->expects($this->once())->method('beginTransaction');
        $pdo->expects($this->once())->method('commit');
        $result = $connection->transaction(function ($db) {
            return $db;
        });
        $this->assertEquals($connection, $result);
    }


    public function testTransactionMethodRollsbackAndThrows()
    {
        $pdo = $this->getMockBuilder('DatabaseConnectionTestMockPDO')->setMethods(array('beginTransaction', 'commit', 'rollBack'))->getMock();
        $connection = new \Database\Connection($pdo);
        $pdo->expects($this->once())->method('beginTransaction');
        $pdo->expects($this->once())->method('rollBack');
        $pdo->expects($this->never())->method('commit');
        try {
            $connection->transaction(function () {
                throw new Exception('foo');
            });
        } catch (Exception $e) {
            $this->assertEquals('foo', $e->getMessage());
        }
    }


    public function testFromCreatesNewQueryBuilder()
    {
        $conn = new \Database\Connection(new DatabaseConnectionTestMockPDO);
        $builder = $conn->table('users');
        $this->assertInstanceOf('Database\Query\Builder', $builder);
        $this->assertEquals('users', $builder->from);
    }


    public function testPrepareBindings()
    {
        $date = m::mock('DateTime');
        $date->shouldReceive('format')->once()->with('foo')->andReturn('bar');
        $bindings = array('test' => $date);
        $conn = new \Database\Connection(new DatabaseConnectionTestMockPDO);
        $grammar = m::mock('Database\Query\Grammars\Grammar');
        $grammar->shouldReceive('getDateFormat')->once()->andReturn('foo');
        $conn->setQueryGrammar($grammar);
        $result = $conn->prepareBindings($bindings);
        $this->assertEquals(array('test' => 'bar'), $result);
    }


    public function testPretendOnlyLogsQueries()
    {
        $connection = $this->getMockConnection();

        $connection->expects($this->never())->method('getReadPdo');
        $connection->expects($this->never())->method('getPdo');

        $connection->pretend(function ($connection) {
            $connection->fetchAll('foo bar', array('baz'));
        });
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

        $this->assertEquals('baz', $connection->insertUpdate('testTable', array('foo'), array('bar')));
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

        $this->assertEquals('baz', $connection->{$type}('testTable', array('foo')));
    }

    public function testQuoteInto()
    {
        $pdo = $this->createMock('DatabaseConnectionTestMockPDO', array('quote'));
        $connection = new \Database\Connection($pdo);

        $pdo->expects($this->at(0))->method('quote')->with('foo')->willReturn('`foo`');
        $pdo->expects($this->at(1))->method('quote')->with('bar')->willReturn('`bar`');

        $string = $connection->quoteInto('col1 = ? AND col2 = ?', array('foo', 'bar'));

        $this->assertEquals('col1 = `foo` AND col2 = `bar`', $string);
    }

    public function testQuote()
    {
        $pdo = $this->createMock('DatabaseConnectionTestMockPDO', array('quote'));
        $connection = new \Database\Connection($pdo);

        $pdo->expects($this->once())->method('quote')->with('foo')->willReturn('`foo`');

        $string = $connection->quote('foo');

        $this->assertEquals('`foo`', $string);
    }

    public function testQuoteArray()
    {
        $pdo = $this->createMock('DatabaseConnectionTestMockPDO', array('quote'));
        $connection = new \Database\Connection($pdo);

        $pdo->expects($this->at(0))->method('quote')->with('foo')->willReturn('`foo`');
        $pdo->expects($this->at(1))->method('quote')->with('bar')->willReturn('`bar`');

        $array = $connection->quote(array('foo', 'bar'));

        $this->assertEquals(array('`foo`', '`bar`'), $array);
    }

    public function testSetAndGetPrefix()
    {
        $connection = new \Database\Connection(new DatabaseConnectionTestMockPDO);

        $connection->setTablePrefix('foo');
        $this->assertEquals('foo', $connection->getTablePrefix());

        $connection->setTablePrefix('bar');
        $this->assertEquals('bar', $connection->getTablePrefix());
    }

    public function testItCorrectlyEnablesAndDisablesLogging()
    {
        $connection = new \Database\Connection(new DatabaseConnectionTestMockPDO);

        $connection->enableQueryLog();
        $this->assertEquals(true, $connection->logging());

        $connection->disableQueryLog();
        $this->assertEquals(false, $connection->logging());
    }

    public function testItCorrectlySetsTheFetchMode()
    {
        $connection = new \Database\Connection(new DatabaseConnectionTestMockPDO);

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
        $builder = $this->getMockBuilder('Database\Connection')->setConstructorArgs(array($pdo));

        if ($methods) {
            $builder->setMethods($methods);
        }

        return $builder->getMock();
    }
}

class DatabaseConnectionTestMockPDO extends PDO
{
    public function __construct()
    {
    }
}
