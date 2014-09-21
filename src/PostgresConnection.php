<?php namespace Illuminate\Database;

use Illuminate\Database\Query\Grammars\PostgresGrammar as QueryGrammar;

class PostgresConnection extends Connection {

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
}
