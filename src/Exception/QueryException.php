<?php namespace Database\Exception;

use PDOException;

class QueryException extends PDOException
{
    /**
     * Create a new query exception instance.
     *
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message, \Exception $previous)
    {
        parent::__construct($message);

        $this->code = $previous->getCode();

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }
}
