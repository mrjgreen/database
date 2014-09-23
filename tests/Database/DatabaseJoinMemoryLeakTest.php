<?php

use Mockery as m;
use Illuminate\Database\Query\Builder;

class DatabaseJoinMemoryLeakTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }


    public function testItDoesNotLeakMemoryOnNewQuery()
    {
        $i = 5;

        $builderMain = $this->getBuilder();

        $last = null;

        while($i--)
        {
            $builder = $builderMain->newQuery();
            $builder->select('*')->from('users');

            $prev = $last;
            $last = memory_get_usage();
        }

        $this->assertEquals($prev, $last);

    }


    public function testItDoesNotLeakMemoryOnNewQueryWithJoin()
    {
        $i = 5;

        $builderMain = $this->getBuilder();

        $last = null;

        while($i--)
        {
            $builder = $builderMain->newQuery();
            $builder->select('*')->join('new','col','=','col2')->from('users');

            $prev = $last;
            $last = memory_get_usage();
        }

        $this->assertEquals($prev, $last);
    }


    protected function getBuilder()
    {
        $grammar = new Illuminate\Database\Query\Grammars\Grammar;
        return new Builder(m::mock('Illuminate\Database\ConnectionInterface'), $grammar);
    }


}
