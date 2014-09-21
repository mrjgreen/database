<?php namespace Illuminate\Database;

use Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;

class MySqlConnection extends Connection {

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Illuminate\Database\Query\Grammars\MySqlGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}
}
