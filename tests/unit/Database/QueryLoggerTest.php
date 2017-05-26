<?php

class QueryLoggerTest extends \PHPUnit\Framework\TestCase {

    private $logMessages = array(
        array('message', array('foo' => 'bar')),
        array('message2', array('bar' => 'foo')),
    );

	public function testItStoresAndLogsQueries()
	{
		$log = new \Database\QueryLogger();

        foreach($this->logMessages as $messages)
        {
            $log->debug($messages[0], $messages[1]);
        }

        $this->assertEquals($this->logMessages, $log->getQueryLog());

        // Should be able to fetch the messages more than once
        $this->assertEquals($this->logMessages, $log->getQueryLog());
	}

    public function testItFlushesQueries()
	{
		$log = new \Database\QueryLogger();

        foreach($this->logMessages as $messages)
        {
            $log->debug($messages[0], $messages[1]);
        }

        $this->assertEquals(array(), $log->flushQueryLog()->getQueryLog());
	}
}
