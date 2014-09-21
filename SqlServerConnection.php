<?php namespace Illuminate\Database;

use Illuminate\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;

class SqlServerConnection extends Connection {

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\SqlServerGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
}
