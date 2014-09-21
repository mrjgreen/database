<?php namespace Illuminate\Database;

use Illuminate\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;

class SQLiteConnection extends Connection {

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\SQLiteGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
}
