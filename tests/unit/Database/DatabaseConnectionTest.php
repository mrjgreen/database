<?php

use Mockery as m;

class DatabaseConnectionTest extends \PHPUnit\Framework\TestCase {

	protected function tearDown(): void
	{
		m::close();
	}

    private function getMockPdoAndStatement($query, array $args = array()) {
        $pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('prepare'))->getMock();
        $statement = $this->getMockBuilder(PDOStatement::class)->setMethods(array('execute', 'fetchColumn'))->getMock();
        $pdo->expects($this->once())->method('prepare')->with($this->equalTo($query))->will($this->returnValue($statement));
        $statement->expects($this->once())->method('execute')->with($this->equalTo($args));
        return array($pdo, $statement);
    }


	public function testFetchOneCallsSelectAndReturnsSingleResult()
	{
        list($pdo, $statement) = $this->getMockPdoAndStatement('foo', array('bar' => 'baz'));
        $connection = new \Database\Connection($pdo);

        $statement->expects($this->once())->method('fetchColumn')->will($this->returnValue('boom'));

		$result = $connection->fetchOne('foo', array('bar' => 'baz'));
		$this->assertEquals('boom', $result);
	}


	public function testFetchProperlyCallsPDO()
	{
		$pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('prepare'))->getMock();
		$writePdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('prepare', 'inTransaction'))->getMock();
		$writePdo->expects($this->never())->method('prepare');
		$writePdo->expects($this->exactly(2))->method('inTransaction')->willReturn(false);
		$statement = $this->getMockBuilder(PDOStatement::class)->setMethods(array('execute', 'fetch'))->getMock();
		$statement->expects($this->once())->method('execute')->with($this->equalTo(array('foo' => 'bar')));
		$statement->expects($this->once())->method('fetch')->will($this->returnValue(array('boom')));
		$pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
		$connection = new \Database\Connection($writePdo);
		$connection->setReadPdo($pdo);
		$connection->setPdo($writePdo);
		$results = $connection->fetch('foo', array('foo' => 'bar'));
		$this->assertEquals(array('boom'), $results);
	}

	public function testQueryProperlyCallsPDO()
	{
        list($pdo, $statement) = $this->getMockPdoAndStatement('foo', array('bar'));
        $connection = new \Database\Connection($pdo);

		$results = $connection->query('foo', array('bar'));
		$this->assertInstanceOf('PDOStatement', $results);
	}


	public function testTransactionMethodRunsSuccessfully()
	{
		$pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('beginTransaction','commit'))->getMock();
		$mock = new \Database\Connection($pdo);
		$pdo->expects($this->once())->method('beginTransaction');
		$pdo->expects($this->once())->method('commit');
		$result = $mock->transaction(function($db) { return $db; });
		$this->assertEquals($mock, $result);
	}


	public function testTransactionMethodRollsbackAndThrows()
	{
		$pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('beginTransaction','commit','rollback'))->getMock();
		$mock = new \Database\Connection($pdo);
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

    public function testItProxiesInsertToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('insert', 'insert');
    }

    public function testItProxiesInsertIgnoreToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('insertIgnore', 'insert ignore');
    }

    public function testItProxiesReplaceToBuilder()
    {
        $this->doInsertTypeProxyCallsToBuilder('replace', 'replace');
    }

    public function testItProxiesDeleteToBuilder()
    {
        list($pdo, $statement) = $this->getMockPdoAndStatement('delete from "testTable" where foo = ?', array('bar'));
        $connection = new \Database\Connection($pdo);

        $connection->delete('testTable', 'foo = ?', array('bar'));
    }

    public function testItProxiesUpdateToBuilder()
    {
        list($pdo, $statement) = $this->getMockPdoAndStatement('update "testTable" set "fuzz" = ? where foo = ?', array('buzz','bar'));
        $connection = new \Database\Connection($pdo);

        $res = $connection->update('testTable', array('fuzz' => 'buzz'), 'foo = ?', array('bar'));

        $this->assertSame($statement, $res);
    }

    public function testItProxiesInsertUpdateToBuilder()
    {
        list($pdo, $statement) = $this->getMockPdoAndStatement(
            "insert into `testTable` (`foo`) values (?) on duplicate key update `bar` = ?", 
            array('a', 'b')
        );
        $connection = new \Database\Connection($pdo, new \Database\Query\Grammars\MySqlGrammar());

        $res = $connection->insertUpdate('testTable', array('foo' => 'a'), array('bar' => 'b'));

        $this->assertSame($statement, $res);
    }

    private function doInsertTypeProxyCallsToBuilder($type, $sql)
    {
        list($pdo, $statement) = $this->getMockPdoAndStatement("$sql into `testTable` (`foo`) values (?)", array('a'));
        $connection = new \Database\Connection($pdo, new \Database\Query\Grammars\MySqlGrammar());

        $res = $connection->{$type}('testTable', array('foo' => 'a'));

        $this->assertSame($statement, $res);
    }

    public function testQuoteInto()
    {
        $pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('quote'))->getMock();

        $connection = new \Database\Connection($pdo, new \Database\Query\Grammars\MySqlGrammar());

        $pdo
            ->expects($this->exactly(2))
            ->method('quote')
            ->withConsecutive(array('foo'), array('bar'))
            ->willReturnOnConsecutiveCalls('`foo`', '`bar`');

        $string = $connection->quoteInto('col1 = ? AND col2 = ?', array('foo', 'bar'));

        $this->assertEquals('col1 = `foo` AND col2 = `bar`', $string);
    }

    public function testQuote()
    {
		$pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('quote'))->getMock();
        $connection = new \Database\Connection($pdo);

        $pdo->expects($this->once())->method('quote')->with('foo')->willReturn('`foo`');

        $string = $connection->quote('foo');

        $this->assertEquals('`foo`', $string);
    }

    public function testQuoteArray()
    {
		$pdo = $this->getMockBuilder(DatabaseConnectionTestMockPDO::class)->setMethods(array('quote'))->getMock();
        $connection = new \Database\Connection($pdo);

        $pdo
            ->expects($this->exactly(2))
            ->method('quote')
            ->withConsecutive(array('foo'), array('bar'))
            ->willReturnOnConsecutiveCalls('`foo`', '`bar`');

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
        return new \Database\Connection($pdo);
	}
}

class DatabaseConnectionTestMockPDO extends PDO { public function __construct() {} }
