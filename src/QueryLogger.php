<?php namespace Database;

use Psr\Log\AbstractLogger;

class QueryLogger extends AbstractLogger
{
    private $queryLog = array();

    /**
     * {inherit}
     */
    public function log($level, $message, array $context = array())
    {
        $this->queryLog[] = array($message, $context);
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return LogArray
     */
    public function flushQueryLog()
    {
        $this->queryLog = array();

        return $this;
    }
}
