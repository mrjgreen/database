<?php

use Mockery as m;
use Database\Query\Builder;
use Database\Query\Expression as Raw;

class DatabaseQueryBuilderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testBasicSelect()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users');
		$this->assertEquals('select * from "users"', $builder->toSql());
	}


	public function testBasicTableWrappingProtectsQuotationMarks()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('some"table');
		$this->assertEquals('select * from "some""table"', $builder->toSql());
	}


	public function testAddingSelects()
	{
		$builder = $this->getBuilder();
		$builder->select('foo')->addSelect('bar')->addSelect(array('baz', 'boom'))->from('users');
		$this->assertEquals('select "foo", "bar", "baz", "boom" from "users"', $builder->toSql());
	}


	public function testBasicSelectWithPrefix()
	{
		$builder = $this->getBuilder();
		$builder->getGrammar()->setTablePrefix('prefix_');
		$builder->select('*')->from('users');
		$this->assertEquals('select * from "prefix_users"', $builder->toSql());
	}


	public function testBasicSelectDistinct()
	{
		$builder = $this->getBuilder();
		$builder->distinct()->select('foo', 'bar')->from('users');
		$this->assertEquals('select distinct "foo", "bar" from "users"', $builder->toSql());
	}

    public function testSubSelectRawString()
    {
        $builder = $this->getBuilder();
        $builder->selectSub('select * from test', 'tmp')->from('users');
        $this->assertEquals('select (select * from test) as "tmp" from "users"', $builder->toSql());
    }

    public function testSubSelectClosure()
    {
        $builder = $this->getBuilder();

        $builder->selectSub(function($build){
            $build->select('foo')->where('bar','=','baz')->from('test');
        }, 'tmp')->from('users');

        $this->assertEquals('select (select "foo" from "test" where "bar" = ?) as "tmp" from "users"', $builder->toSql());
        $this->assertEquals(array('baz'), $builder->getBindings());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSubSelectThrowsInvalidArgument()
    {
        $builder = $this->getBuilder();

        $builder->selectSub(array('test'), 'tmp')->from('users');
    }

	public function testBasicAlias()
	{
		$builder = $this->getBuilder();
		$builder->select('foo as bar')->from('users');
		$this->assertEquals('select "foo" as "bar" from "users"', $builder->toSql());
	}


	public function testBasicTableWrapping()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('public.users');
		$this->assertEquals('select * from "public"."users"', $builder->toSql());
	}


	public function testBasicWheres()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$this->assertEquals('select * from "users" where "id" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}

    public function testArrayWheres()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where(array('id' => 1, 'name' => 2));
        $this->assertEquals('select * from "users" where ("id" = ? and "name" = ?)', $builder->toSql());
        $this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());
    }

	public function testMySqlWrappingProtectsQuotationMarks()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->From('some`table');
		$this->assertEquals('select * from `some``table`', $builder->toSql());
	}


	public function testWhereDayMySql()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('users')->whereDay('created_at', '=', 1);
		$this->assertEquals('select * from `users` where day(`created_at`) = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testWhereMonthMySql()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('users')->whereMonth('created_at', '=', 5);
		$this->assertEquals('select * from `users` where month(`created_at`) = ?', $builder->toSql());
		$this->assertEquals(array(0 => 5), $builder->getBindings());
	}


	public function testWhereYearMySql()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('users')->whereYear('created_at', '=', 2014);
		$this->assertEquals('select * from `users` where year(`created_at`) = ?', $builder->toSql());
		$this->assertEquals(array(0 => 2014), $builder->getBindings());
	}


	public function testWhereDayPostgres()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereDay('created_at', '=', 1);
		$this->assertEquals('select * from "users" where day("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testWhereMonthPostgres()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereMonth('created_at', '=', 5);
		$this->assertEquals('select * from "users" where month("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 5), $builder->getBindings());
	}


	public function testWhereYearPostgres()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereYear('created_at', '=', 2014);
		$this->assertEquals('select * from "users" where year("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 2014), $builder->getBindings());
	}


	public function testWhereDaySqlite()
	{
		$builder = $this->getSQLiteBuilder();
		$builder->select('*')->from('users')->whereDay('created_at', '=', 1);
		$this->assertEquals('select * from "users" where strftime(\'%d\', "created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testWhereMonthSqlite()
	{
		$builder = $this->getSQLiteBuilder();
		$builder->select('*')->from('users')->whereMonth('created_at', '=', 5);
		$this->assertEquals('select * from "users" where strftime(\'%m\', "created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 5), $builder->getBindings());
	}


	public function testWhereYearSqlite()
	{
		$builder = $this->getSQLiteBuilder();
		$builder->select('*')->from('users')->whereYear('created_at', '=', 2014);
		$this->assertEquals('select * from "users" where strftime(\'%Y\', "created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 2014), $builder->getBindings());
	}


	public function testWhereDaySqlServer()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereDay('created_at', '=', 1);
		$this->assertEquals('select * from "users" where day("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testWhereMonthSqlServer()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereMonth('created_at', '=', 5);
		$this->assertEquals('select * from "users" where month("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 5), $builder->getBindings());
	}


	public function testWhereYearSqlServer()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('users')->whereYear('created_at', '=', 2014);
		$this->assertEquals('select * from "users" where year("created_at") = ?', $builder->toSql());
		$this->assertEquals(array(0 => 2014), $builder->getBindings());
	}


	public function testWhereBetweens()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereBetween('id', array(1, 2));
		$this->assertEquals('select * from "users" where "id" between ? and ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereNotBetween('id', array(1, 2));
		$this->assertEquals('select * from "users" where "id" not between ? and ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());
	}


	public function testBasicOrWheres()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1)->orWhere('email', '=', 'foo');
		$this->assertEquals('select * from "users" where "id" = ? or "email" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 'foo'), $builder->getBindings());
	}


	public function testRawWheres()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereRaw('id = ? or email = ?', array(1, 'foo'));
		$this->assertEquals('select * from "users" where id = ? or email = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 'foo'), $builder->getBindings());
	}


	public function testRawOrWheres()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1)->orWhereRaw('email = ?', array('foo'));
		$this->assertEquals('select * from "users" where "id" = ? or email = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 'foo'), $builder->getBindings());
	}


	public function testBasicWhereIns()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereIn('id', array(1, 2, 3));
		$this->assertEquals('select * from "users" where "id" in (?, ?, ?)', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2, 2 => 3), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1)->orWhereIn('id', array(1, 2, 3));
		$this->assertEquals('select * from "users" where "id" = ? or "id" in (?, ?, ?)', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 1, 2 => 2, 3 => 3), $builder->getBindings());
	}


	public function testBasicWhereNotIns()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereNotIn('id', array(1, 2, 3));
		$this->assertEquals('select * from "users" where "id" not in (?, ?, ?)', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2, 2 => 3), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1)->orWhereNotIn('id', array(1, 2, 3));
		$this->assertEquals('select * from "users" where "id" = ? or "id" not in (?, ?, ?)', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 1, 2 => 2, 3 => 3), $builder->getBindings());
	}


	public function testUnions()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$builder->union($this->getBuilder()->select('*')->from('users')->where('id', '=', 2));
		$this->assertEquals('select * from "users" where "id" = ? union select * from "users" where "id" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());

		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$builder->union($this->getMySqlBuilder()->select('*')->from('users')->where('id', '=', 2));
		$this->assertEquals('(select * from `users` where `id` = ?) union (select * from `users` where `id` = ?)', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());
	}


	public function testUnionAlls()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$builder->unionAll($this->getBuilder()->select('*')->from('users')->where('id', '=', 2));
		$this->assertEquals('select * from "users" where "id" = ? union all select * from "users" where "id" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2), $builder->getBindings());
	}


	public function testMultipleUnions()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$builder->union($this->getBuilder()->select('*')->from('users')->where('id', '=', 2));
		$builder->union($this->getBuilder()->select('*')->from('users')->where('id', '=', 3));
		$this->assertEquals('select * from "users" where "id" = ? union select * from "users" where "id" = ? union select * from "users" where "id" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2, 2 => 3), $builder->getBindings());
	}


	public function testMultipleUnionAlls()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1);
		$builder->unionAll($this->getBuilder()->select('*')->from('users')->where('id', '=', 2));
		$builder->unionAll($this->getBuilder()->select('*')->from('users')->where('id', '=', 3));
		$this->assertEquals('select * from "users" where "id" = ? union all select * from "users" where "id" = ? union all select * from "users" where "id" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 2, 2 => 3), $builder->getBindings());
	}


	public function testSubSelectWhereIns()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereIn('id', function($q)
		{
			$q->select('id')->from('users')->where('age', '>', 25)->limit(3);
		});
		$this->assertEquals('select * from "users" where "id" in (select "id" from "users" where "age" > ? limit 3)', $builder->toSql());
		$this->assertEquals(array(25), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereNotIn('id', function($q)
		{
			$q->select('id')->from('users')->where('age', '>', 25)->limit(3);
		});
		$this->assertEquals('select * from "users" where "id" not in (select "id" from "users" where "age" > ? limit 3)', $builder->toSql());
		$this->assertEquals(array(25), $builder->getBindings());
	}


	public function testBasicWhereNulls()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereNull('id');
		$this->assertEquals('select * from "users" where "id" is null', $builder->toSql());
		$this->assertEquals(array(), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '=', 1)->orWhereNull('id');
		$this->assertEquals('select * from "users" where "id" = ? or "id" is null', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testBasicWhereNotNulls()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->whereNotNull('id');
		$this->assertEquals('select * from "users" where "id" is not null', $builder->toSql());
		$this->assertEquals(array(), $builder->getBindings());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', '>', 1)->orWhereNotNull('id');
		$this->assertEquals('select * from "users" where "id" > ? or "id" is not null', $builder->toSql());
		$this->assertEquals(array(0 => 1), $builder->getBindings());
	}


	public function testGroupBys()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->groupBy('id', 'email');
		$this->assertEquals('select * from "users" group by "id", "email"', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->groupBy(['id', 'email']);
		$this->assertEquals('select * from "users" group by "id", "email"', $builder->toSql());
	}


	public function testOrderBys()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->orderBy('email')->orderBy('age', 'desc');
		$this->assertEquals('select * from "users" order by "email" asc, "age" desc', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->orderBy('email')->orderByRaw('"age" ? desc', array('foo'));
		$this->assertEquals('select * from "users" order by "email" asc, "age" ? desc', $builder->toSql());
		$this->assertEquals(array('foo'), $builder->getBindings());
	}


	public function testHavings()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->having('email', '>', 1);
		$this->assertEquals('select * from "users" having "email" > ?', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->groupBy('email')->having('email', '>', 1);
		$this->assertEquals('select * from "users" group by "email" having "email" > ?', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('email as foo_email')->from('users')->having('foo_email', '>', 1);
		$this->assertEquals('select "email" as "foo_email" from "users" having "foo_email" > ?', $builder->toSql());
	}


	public function testRawHavings()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->havingRaw('user_foo < user_bar');
		$this->assertEquals('select * from "users" having user_foo < user_bar', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->having('baz', '=', 1)->orHavingRaw('user_foo < user_bar');
		$this->assertEquals('select * from "users" having "baz" = ? or user_foo < user_bar', $builder->toSql());
	}


	public function testLimitsAndOffsets()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->offset(5)->limit(10);
		$this->assertEquals('select * from "users" limit 10 offset 5', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->offset(5)->limit(10);
		$this->assertEquals('select * from "users" limit 10 offset 5', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->offset(-5)->limit(10);
		$this->assertEquals('select * from "users" limit 10 offset 0', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->forPage(2, 15);
		$this->assertEquals('select * from "users" limit 15 offset 15', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->forPage(-2, 15);
		$this->assertEquals('select * from "users" limit 15 offset 0', $builder->toSql());
	}


	public function testWhereShortcut()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('id', 1)->orWhere('name', 'foo');
		$this->assertEquals('select * from "users" where "id" = ? or "name" = ?', $builder->toSql());
		$this->assertEquals(array(0 => 1, 1 => 'foo'), $builder->getBindings());
	}


	public function testNestedWheres()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('email', '=', 'foo')->orWhere(function($q)
		{
			$q->where('name', '=', 'bar')->where('age', '=', 25);
		});
		$this->assertEquals('select * from "users" where "email" = ? or ("name" = ? and "age" = ?)', $builder->toSql());
		$this->assertEquals(array(0 => 'foo', 1 => 'bar', 2 => 25), $builder->getBindings());
	}


	public function testFullSubSelects()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('email', '=', 'foo')->orWhere('id', '=', function($q)
		{
			$q->select(new Raw('max(id)'))->from('users')->where('email', '=', 'bar');
		});

		$this->assertEquals('select * from "users" where "email" = ? or "id" = (select max(id) from "users" where "email" = ?)', $builder->toSql());
		$this->assertEquals(array(0 => 'foo', 1 => 'bar'), $builder->getBindings());
	}


	public function testWhereExists()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('orders')->whereExists(function($q)
		{
			$q->select('*')->from('products')->where('products.id', '=', new Raw('"orders"."id"'));
		});
		$this->assertEquals('select * from "orders" where exists (select * from "products" where "products"."id" = "orders"."id")', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('orders')->whereNotExists(function($q)
		{
			$q->select('*')->from('products')->where('products.id', '=', new Raw('"orders"."id"'));
		});
		$this->assertEquals('select * from "orders" where not exists (select * from "products" where "products"."id" = "orders"."id")', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('orders')->where('id', '=', 1)->orWhereExists(function($q)
		{
			$q->select('*')->from('products')->where('products.id', '=', new Raw('"orders"."id"'));
		});
		$this->assertEquals('select * from "orders" where "id" = ? or exists (select * from "products" where "products"."id" = "orders"."id")', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('orders')->where('id', '=', 1)->orWhereNotExists(function($q)
		{
			$q->select('*')->from('products')->where('products.id', '=', new Raw('"orders"."id"'));
		});
		$this->assertEquals('select * from "orders" where "id" = ? or not exists (select * from "products" where "products"."id" = "orders"."id")', $builder->toSql());
	}


	public function testBasicJoins()
	{
        foreach(array('left join' => 'leftJoin', 'right join' => 'rightJoin') as $joinQuery => $joinType)
        {
            $builder = $this->getBuilder();
            $builder->select('*')->from('users')->join('contacts', 'users.id', '=', 'contacts.id')->$joinType('photos', 'users.id', '=', 'photos.id');
            $this->assertEquals('select * from "users" inner join "contacts" on "users"."id" = "contacts"."id" '.$joinQuery.' "photos" on "users"."id" = "photos"."id"', $builder->toSql());

            $builder = $this->getBuilder();
            $builder->select('*')->from('users')->{$joinType . 'Where'}('photos', 'users.id', '=', 'bar')->joinWhere('photos', 'users.id', '=', 'foo');
            $this->assertEquals('select * from "users" '.$joinQuery.' "photos" on "users"."id" = ? inner join "photos" on "users"."id" = ?', $builder->toSql());
            $this->assertEquals(array('bar', 'foo'), $builder->getBindings());
        }

	}




	public function testComplexJoin()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->join('contacts', function($j)
		{
			$j->on('users.id', '=', 'contacts.id')->orOn('users.name', '=', 'contacts.name');
		});
		$this->assertEquals('select * from "users" inner join "contacts" on "users"."id" = "contacts"."id" or "users"."name" = "contacts"."name"', $builder->toSql());

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->join('contacts', function($j)
		{
			$j->where('users.id', '=', 'foo')->orWhere('users.name', '=', 'bar');
		});
		$this->assertEquals('select * from "users" inner join "contacts" on "users"."id" = ? or "users"."name" = ?', $builder->toSql());
		$this->assertEquals(array('foo', 'bar'), $builder->getBindings());
	}

	public function testJoinWhereNull()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->join('contacts', function($j)
		{
			$j->on('users.id', '=', 'contacts.id')->whereNull('contacts.deleted_at');
		});
		$this->assertEquals('select * from "users" inner join "contacts" on "users"."id" = "contacts"."id" and "contacts"."deleted_at" is null', $builder->toSql());
	}

	public function testRawExpressionsInSelect()
	{
		$builder = $this->getBuilder();
		$builder->select(new Raw('substr(foo, 6)'))->from('users');
		$this->assertEquals('select substr(foo, 6) from "users"', $builder->toSql());
	}


	public function testFindReturnsFirstResultByID()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select * from "users" where "id" = ? limit 1', array(1))->andReturn(array(array('foo' => 'bar')));
		$results = $builder->from('users')->find(1);
		$this->assertEquals(array('foo' => 'bar'), $results);
	}


	public function testFirstMethodReturnsFirstResult()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select * from "users" where "id" = ? limit 1', array(1))->andReturn(array(array('foo' => 'bar')));
		$results = $builder->from('users')->where('id', '=', 1)->first();
		$this->assertEquals(array('foo' => 'bar'), $results);
	}


	public function testListMethodsGetsArrayOfColumnValues()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->andReturn(array(array('foo' => 'bar'), array('foo' => 'baz')));
		$results = $builder->from('users')->where('id', '=', 1)->lists('foo');
		$this->assertEquals(array('bar', 'baz'), $results);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->andReturn(array(array('id' => 1, 'foo' => 'bar'), array('id' => 10, 'foo' => 'baz')));
		$results = $builder->from('users')->where('id', '=', 1)->lists('foo', 'id');
		$this->assertEquals(array(1 => 'bar', 10 => 'baz'), $results);
	}


	public function testImplode()
	{
		// Test without glue.
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->andReturn(array(array('foo' => 'bar'), array('foo' => 'baz')));
		$results = $builder->from('users')->where('id', '=', 1)->implode('foo');
		$this->assertEquals('barbaz', $results);

		// Test with glue.
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->andReturn(array(array('foo' => 'bar'), array('foo' => 'baz')));
		$results = $builder->from('users')->where('id', '=', 1)->implode('foo', ',');
		$this->assertEquals('bar,baz', $results);
	}

	public function testPluckMethodReturnsSingleColumn()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select "foo" from "users" where "id" = ? limit 1', array(1))->andReturn(array(array('foo' => 'bar')));
		$results = $builder->from('users')->where('id', '=', 1)->pluck('foo');
		$this->assertEquals('bar', $results);
	}


	public function testAggregateFunctions()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count(*) as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$results = $builder->from('users')->count();
		$this->assertEquals(1, $results);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count(*) as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$results = $builder->from('users')->exists();
		$this->assertTrue($results);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select max("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$results = $builder->from('users')->max('id');
		$this->assertEquals(1, $results);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select min("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$results = $builder->from('users')->min('id');
		$this->assertEquals(1, $results);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select sum("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$results = $builder->from('users')->sum('id');
		$this->assertEquals(1, $results);
	}


	public function testAggregateResetFollowedByGet()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count(*) as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select sum("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 2)));
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select "column1", "column2" from "users"', array())->andReturn(array(array('column1' => 'foo', 'column2' => 'bar')));
		$builder->from('users')->select('column1', 'column2');
		$count = $builder->count();
		$this->assertEquals(1, $count);
		$sum = $builder->sum('id');
		$this->assertEquals(2, $sum);
		$result = $builder->get();
		$this->assertEquals(array(array('column1' => 'foo', 'column2' => 'bar')), $result);
	}


	public function testAggregateResetFollowedBySelectGet()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count("column1") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select "column2", "column3" from "users"', array())->andReturn(array(array('column2' => 'foo', 'column3' => 'bar')));
		$builder->from('users');
		$count = $builder->count('column1');
		$this->assertEquals(1, $count);
		$result = $builder->select('column2', 'column3')->get();
		$this->assertEquals(array(array('column2' => 'foo', 'column3' => 'bar')), $result);
	}


	public function testAggregateResetFollowedByGetWithColumns()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count("column1") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
		$builder->getConnection()->shouldReceive('fetchAll')->once()->with('select "column2", "column3" from "users"', array())->andReturn(array(array('column2' => 'foo', 'column3' => 'bar')));
		$builder->from('users');
		$count = $builder->count('column1');
		$this->assertEquals(1, $count);
		$result = $builder->get(array('column2', 'column3'));
		$this->assertEquals(array(array('column2' => 'foo', 'column3' => 'bar')), $result);
	}

	public function testInsertSelectOnDuplicateKeyRawMethod()
	{
		$builder = $this->getMySqlBuilder();

		$builder->getConnection()->shouldReceive('query')->once()
			->with('insert into `users` (`email`, `name`) select `email`, `name` from `admin` where `name` = ? on duplicate key update `total` = ?, `email` = VALUES(email)', array('John', 1))
			->andReturn(true);

		$select = $builder->newQuery()
			->from('admin')
			->select('email', 'name')
			->where('name','John');

		$result = $builder
			->from('users')
			->insertSelectOnDuplicateKeyUpdate(
				$select,
				array('email', 'name'),
				array('total' => 1, 'email' => new \Database\Query\Expression('VALUES(email)'))
			);

		$this->assertTrue($result);
	}

    public function doTestInsertSelectMethod($query, $method)
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('query')->once()
            ->with($query . ' into `users` (`email`) select `email` from `admin` where `name` = ?', array('foo'))
            ->andReturn(true);

        $select = $builder->newQuery()
            ->from('admin')
            ->select('email')
            ->where('name','foo');

        $result = $builder
            ->from('users')
            ->{$method}($select, array('email'));

        $this->assertTrue($result);
    }

    public function testInsertSelectMethodClosure()
    {
        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('query')->once()
            ->with('insert into "users" ("email") select "email" from "admin" where "name" = ?', array('foo'))
            ->andReturn(true);

        $result = $builder
            ->from('users')
            ->insertSelect(function($select){
                $select->from('admin')
                        ->select('email')
                        ->where('name','foo');
            }, array('email'));

        $this->assertTrue($result);
    }

    public function testInsertSelectMethod()
    {
        $this->doTestInsertSelectMethod("insert", "insertSelect");
    }

    public function testInsertIgnoreSelectMethod()
    {
        $this->doTestInsertSelectMethod("insert ignore", "insertIgnoreSelect");
    }

    public function testReplaceSelectMethod()
    {
        $this->doTestInsertSelectMethod("replace", "replaceSelect");
    }

	public function testInsertMethod()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('insert into "users" ("email") values (?)', array('foo'))->andReturn(true);
		$result = $builder->from('users')->insert(array('email' => 'foo'));
		$this->assertTrue($result);
	}

    public function testReplaceMethod()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('query')->once()->with('replace into `users` (`email`) values (?)', array('foo'))->andReturn(true);
        $result = $builder->from('users')->replace(array('email' => 'foo'));
        $this->assertTrue($result);
    }

    public function testInsertIgnoreMethodMySql()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('query')->once()->with('insert ignore into `users` (`email`) values (?)', array('foo'))->andReturn(true);
        $result = $builder->from('users')->insertIgnore(array('email' => 'foo'));
        $this->assertTrue($result);
    }

    public function testInsertIgnoreMethodSqlite()
    {
        $builder = $this->getSQLiteBuilder();
        $builder->getConnection()->shouldReceive('query')->once()->with('insert or ignore into "users" ("email") values (?)', array('foo'))->andReturn(true);
        $result = $builder->from('users')->insertIgnore(array('email' => 'foo'));
        $this->assertTrue($result);
    }

	public function testSQLiteMultipleInserts()
	{
		$builder = $this->getSQLiteBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('insert into "users" ("email", "name") select ? as "email", ? as "name" union select ? as "email", ? as "name"', array('foo', 'taylor', 'bar', 'dayle'))->andReturn(true);
		$result = $builder->from('users')->insert(array(array('email' => 'foo', 'name' => 'taylor'), array('email' => 'bar', 'name' => 'dayle')));
		$this->assertTrue($result);
	}


	public function testInsertGetIdMethod()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('insert into "users" ("email") values (?)', array('foo'));
        $builder->getConnection()->shouldReceive('lastInsertId')->once()->andReturn(1);
		$result = $builder->from('users')->insertGetId(array('email' => 'foo'));
		$this->assertEquals(1, $result);
	}

	public function testInsertMethodRespectsRawBindings()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('insert into "users" ("email") values (CURRENT TIMESTAMP)', array())->andReturn(true);
		$result = $builder->from('users')->insert(array('email' => new Raw('CURRENT TIMESTAMP')));
		$this->assertTrue($result);
	}


	public function testUpdateMethod()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "email" = ?, "name" = ? where "id" = ?', array('foo', 'bar', 1))->andReturn(1);
		$result = $builder->from('users')->where('id', '=', 1)->update(array('email' => 'foo', 'name' => 'bar'));
		$this->assertEquals(1, $result);

		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update `users` set `email` = ?, `name` = ? where `id` = ? order by `foo` desc limit 5', array('foo', 'bar', 1))->andReturn(1);
		$result = $builder->from('users')->where('id', '=', 1)->orderBy('foo', 'desc')->limit(5)->update(array('email' => 'foo', 'name' => 'bar'));
		$this->assertEquals(1, $result);
	}


	public function testUpdateMethodWithJoins()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update "users" inner join "orders" on "users"."id" = "orders"."user_id" set "email" = ?, "name" = ? where "users"."id" = ?', array('foo', 'bar', 1))->andReturn(1);
		$result = $builder->from('users')->join('orders', 'users.id', '=', 'orders.user_id')->where('users.id', '=', 1)->update(array('email' => 'foo', 'name' => 'bar'));
		$this->assertEquals(1, $result);
	}


	public function testUpdateMethodWithoutJoinsOnPostgres()
	{
		$builder = $this->getPostgresBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "email" = ?, "name" = ? where "id" = ?', array('foo', 'bar', 1))->andReturn(1);
		$result = $builder->from('users')->where('id', '=', 1)->update(array('email' => 'foo', 'name' => 'bar'));
		$this->assertEquals(1, $result);
	}


	public function testUpdateMethodWithJoinsOnPostgres()
	{
		$builder = $this->getPostgresBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "email" = ?, "name" = ? from "orders" where "users"."id" = ? and "users"."id" = "orders"."user_id"', array('foo', 'bar', 1))->andReturn(1);
		$result = $builder->from('users')->join('orders', 'users.id', '=', 'orders.user_id')->where('users.id', '=', 1)->update(array('email' => 'foo', 'name' => 'bar'));
		$this->assertEquals(1, $result);
	}


	public function testUpdateMethodRespectsRaw()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "email" = foo, "name" = ? where "id" = ?', array('bar', 1))->andReturn(1);
		$result = $builder->from('users')->where('id', '=', 1)->update(array('email' => new Raw('foo'), 'name' => 'bar'));
		$this->assertEquals(1, $result);
	}

    public function testInsertOnDuplicateKeyMethod()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('query')->once()
            ->with('insert into `users` (`email`) values (?) on duplicate key update `total` = ?', array('foo', 'blah'))->andReturn(true);

        $result = $builder
            ->from('users')
            ->insertOnDuplicateKeyUpdate(array('email' => 'foo'), array('total' => 'blah'));

        $this->assertTrue($result);
    }

    public function testInsertOnDuplicateKeyRawMethod()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('query')->once()
            ->with('insert into `users` (`email`, `name`) values (?, ?), (?, ?) on duplicate key update `total` = `total` + 1, `email` = VALUES(email)', array('foo', 'John', 'bar', 'Smith'))->andReturn(true);
        $result = $builder
            ->from('users')
            ->insertOnDuplicateKeyUpdate(
                array(array('email' => 'foo', 'name' => 'John') , array('email' => 'bar', 'name' => 'Smith')),
                array('total' => new \Database\Query\Expression('`total` + 1'), 'email' => new \Database\Query\Expression('VALUES(email)'))
            );

        $this->assertTrue($result);
    }

    public function testBufferedInsert()
    {
        $statement = $this->getMock('PDOStatement', array('rowCount'));

        $statement->expects($this->exactly(2))
            ->method('rowCount')
            ->will($this->returnValue(2));

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('query')
            ->with('insert into "users" ("email") values (?), (?)', array('foo','bar'))
            ->andReturn($statement);

        $builder->getConnection()->shouldReceive('query')
            ->with('insert into "users" ("email") values (?), (?)', array('fizz','buzz'))
            ->andReturn($statement);

        $result = $builder->from('users')
            ->buffer(2)
            ->insert(new ArrayIterator(array(
                array('email' => 'foo'),
                array('email' => 'bar'),
                array('email' => 'fizz'),
                array('email' => 'buzz'),
            )));

        $this->assertEquals(4, $result);
    }

	public function testDeleteMethod()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('delete from "users" where "email" = ?', array('foo'))->andReturn(1);
		$result = $builder->from('users')->where('email', '=', 'foo')->delete();
		$this->assertEquals(1, $result);

		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('delete from "users" where "id" = ?', array(1))->andReturn(1);
		$result = $builder->from('users')->delete(1);
		$this->assertEquals(1, $result);
	}


	public function testDeleteWithJoinMethod()
	{
		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('delete `users` from `users` inner join `contacts` on `users`.`id` = `contacts`.`id` where `email` = ?', array('foo'))->andReturn(1);
		$result = $builder->from('users')->join('contacts', 'users.id', '=', 'contacts.id')->where('email', '=', 'foo')->delete();
		$this->assertEquals(1, $result);

		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('delete `users` from `users` inner join `contacts` on `users`.`id` = `contacts`.`id` where `id` = ?', array(1))->andReturn(1);
		$result = $builder->from('users')->join('contacts', 'users.id', '=', 'contacts.id')->delete(1);
		$this->assertEquals(1, $result);
	}


	public function testTruncateMethod()
	{
		$builder = $this->getBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with('truncate "users"', array());
		$builder->from('users')->truncate();

		$sqlite = new Database\Query\Grammars\SQLiteGrammar;
		$builder = $this->getBuilder();
		$builder->from('users');
		$this->assertEquals(array(
			'delete from sqlite_sequence where name = ?' => array('users'),
			'delete from "users"' => array(),
		), $sqlite->compileTruncate($builder));
	}

	public function testMySqlWrapping()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('users');
		$this->assertEquals('select * from `users`', $builder->toSql());
	}


	public function testSQLiteOrderBy()
	{
		$builder = $this->getSQLiteBuilder();
		$builder->select('*')->from('users')->orderBy('email', 'desc');
		$this->assertEquals('select * from "users" order by "email" desc', $builder->toSql());
	}


	public function testSqlServerLimitsAndOffsets()
	{
		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('users')->limit(10);
		$this->assertEquals('select top 10 * from [users]', $builder->toSql());

		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('users')->offset(10);
		$this->assertEquals('select * from (select *, row_number() over (order by (select 0)) as row_num from [users]) as temp_table where row_num >= 11', $builder->toSql());

		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('users')->offset(10)->limit(10);
		$this->assertEquals('select * from (select *, row_number() over (order by (select 0)) as row_num from [users]) as temp_table where row_num between 11 and 20', $builder->toSql());

		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('users')->offset(10)->limit(10)->orderBy('email', 'desc');
		$this->assertEquals('select * from (select *, row_number() over (order by [email] desc) as row_num from [users]) as temp_table where row_num between 11 and 20', $builder->toSql());
	}


	public function testMergeWheresCanMergeWheresAndBindings()
	{
		$builder = $this->getBuilder();
		$builder->wheres = array('foo');
		$builder->mergeWheres(array('wheres'), array(12 => 'foo', 13 => 'bar'));
		$this->assertEquals(array('foo', 'wheres'), $builder->wheres);
		$this->assertEquals(array('foo', 'bar'), $builder->getBindings());
	}


	public function testProvidingNullOrFalseAsSecondParameterBuildsCorrectly()
	{
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->where('foo', null);
		$this->assertEquals('select * from "users" where "foo" is null', $builder->toSql());
	}


	/**
	 * @expectedException BadMethodCallException
	 */
	public function testBuilderThrowsExpectedExceptionWithUndefinedMethod()
	{
		$builder = $this->getBuilder();

		$builder->noValidMethodHere();
	}


	public function setupCacheTestQuery($cache, $driver)
	{
		$connection = m::mock('Database\ConnectionInterface');
		$connection->shouldReceive('getName')->andReturn('connection_name');
		$connection->shouldReceive('getCacheManager')->once()->andReturn($cache);
		$cache->shouldReceive('driver')->once()->andReturn($driver);
		$grammar = new Database\Query\Grammars\Grammar;
		$processor = m::mock('Database\Query\Processors\Processor');

		$builder = $this->getMock('Database\Query\Builder', array('getFresh'), array($connection, $grammar, $processor));
		$builder->expects($this->once())->method('getFresh')->with($this->equalTo(array('*')))->will($this->returnValue(array('results')));
		return $builder->select('*')->from('users')->where('email', 'foo@bar.com');
	}


	public function testMySqlLock()
	{
		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock();
		$this->assertEquals('select * from `foo` where `bar` = ? for update', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());

		$builder = $this->getMySqlBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock(false);
		$this->assertEquals('select * from `foo` where `bar` = ? lock in share mode', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());
	}

	public function testMySqlOutfile()
	{
		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with(
			'select * into outfile ? from `foo` where `bar` = ?',
			array('filename', 'baz')
		);
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->intoOutfile('filename');

		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with(
			'select * into outfile ? fields terminated by ? optionally enclosed by ? escaped by ? lines terminated by ? from `foo` where `bar` = ?',
			array('filename',',', '.', '\\', "\n\r", 'baz')
		);
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->intoOutfile('filename', function(\Database\Query\OutfileClause $of){
            $of->enclosedBy(".", true)
                ->escapedBy("\\")
                ->linesTerminatedBy("\n\r")
                ->fieldsTerminatedBy(',');
        });

	}

	public function testMySqlDumpfile()
	{
		$builder = $this->getMySqlBuilder();
		$builder->getConnection()->shouldReceive('query')->once()->with(
			'select * into dumpfile ? from `foo` where `bar` = ?',
			array('filename', 'baz')
		);
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->intoDumpfile('filename');
	}


	public function testPostgresLock()
	{
		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock();
		$this->assertEquals('select * from "foo" where "bar" = ? for update', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());

		$builder = $this->getPostgresBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock(false);
		$this->assertEquals('select * from "foo" where "bar" = ? for share', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());
	}


	public function testSqlServerLock()
	{
		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock();
		$this->assertEquals('select * from [foo] with(rowlock,updlock,holdlock) where [bar] = ?', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());

		$builder = $this->getSqlServerBuilder();
		$builder->select('*')->from('foo')->where('bar', '=', 'baz')->lock(false);
		$this->assertEquals('select * from [foo] with(rowlock,holdlock) where [bar] = ?', $builder->toSql());
		$this->assertEquals(array('baz'), $builder->getBindings());
	}


	public function testBindingOrder()
	{
		$expectedSql = 'select * from "users" inner join "othertable" on "bar" = ? where "registered" = ? group by "city" having "population" > ? order by match ("foo") against(?)';
		$expectedBindings = array('foo', 1, 3, 'bar');

		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->join('othertable', function($join) { $join->where('bar', '=', 'foo'); })->where('registered', 1)->groupBy('city')->having('population', '>', 3)->orderByRaw('match ("foo") against(?)', array('bar'));
		$this->assertEquals($expectedSql, $builder->toSql());
		$this->assertEquals($expectedBindings, $builder->getBindings());

		// order of statements reversed
		$builder = $this->getBuilder();
		$builder->select('*')->from('users')->orderByRaw('match ("foo") against(?)', array('bar'))->having('population', '>', 3)->groupBy('city')->where('registered', 1)->join('othertable', function($join) { $join->where('bar', '=', 'foo'); });
		$this->assertEquals($expectedSql, $builder->toSql());
		$this->assertEquals($expectedBindings, $builder->getBindings());
	}


	public function testAddBindingWithArrayMergesBindings()
	{
		$builder = $this->getBuilder();
		$builder->addBinding(array('foo', 'bar'));
		$builder->addBinding(array('baz'));
		$this->assertEquals(array('foo', 'bar', 'baz'), $builder->getBindings());
	}


	public function testAddBindingWithArrayMergesBindingsInCorrectOrder()
	{
		$builder = $this->getBuilder();
		$builder->addBinding(array('bar', 'baz'), 'having');
		$builder->addBinding(array('foo'), 'where');
		$this->assertEquals(array('foo', 'bar', 'baz'), $builder->getBindings());
	}


	public function testMergeBuilders()
	{
		$builder = $this->getBuilder();
		$builder->addBinding(array('foo', 'bar'));
		$otherBuilder = $this->getBuilder();
		$otherBuilder->addBinding(array('baz'));
		$builder->mergeBindings($otherBuilder);
		$this->assertEquals(array('foo', 'bar', 'baz'), $builder->getBindings());
	}


	public function testMergeBuildersBindingOrder()
	{
		$builder = $this->getBuilder();
		$builder->addBinding('foo', 'where');
		$builder->addBinding('baz', 'having');
		$otherBuilder = $this->getBuilder();
		$otherBuilder->addBinding('bar', 'where');
		$builder->mergeBindings($otherBuilder);
		$this->assertEquals(array('foo', 'bar', 'baz'), $builder->getBindings());
	}

    public function testCountAllRowsBacksUpLimitsOffsetsAndOrders()
    {
        $builder = $this->getBuilder();

        $builder->from('users')->orderBy('baz')->offset(5)->limit(1);

        $builder->getConnection()->shouldReceive('fetchAll')->once()->with('select count(*) as aggregate from "users"',array())
            ->andReturn(array(array('aggregate' => 10)));

        $this->assertEquals(10, $builder->getTotalRowCount());
    }

    public function testIncrementAndDecrement()
    {
        $builder = $this->getBuilder();

        $builder->getConnection()->shouldReceive('raw')->once()->with('"foo" + 1')->andReturn(new \Database\Query\Expression('"foo" + 1'));

        $builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "foo" = "foo" + 1', array());

        $builder->from('users')->increment('foo');


        $builder->getConnection()->shouldReceive('raw')->once()->with('"foo" - 4')->andReturn(new \Database\Query\Expression('"foo" - 4'));

        $builder->getConnection()->shouldReceive('query')->once()->with('update "users" set "foo" = "foo" - 4', array());

        $builder->decrement('foo', 4);
    }

    public function testGettingAndSettingBindings()
    {
        $builder = $this->getBuilder();

        $bindings = array(
            1,
            2,
        );

        $builder->setBindings($bindings);

        $this->assertEquals($bindings, $builder->getBindings());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSettingInvalidBindingThrowsException()
    {
        $builder = $this->getBuilder();

        $builder->setBindings(array(1), 'non-existent');
    }

	protected function getBuilder()
	{
		$grammar = new Database\Query\Grammars\Grammar;
		return new Builder(m::mock('Database\ConnectionInterface'), $grammar);
	}


	protected function getPostgresBuilder()
	{
		$grammar = new Database\Query\Grammars\PostgresGrammar;
		return new Builder(m::mock('Database\ConnectionInterface'), $grammar);
	}


	protected function getMySqlBuilder()
	{
		$grammar = new Database\Query\Grammars\MySqlGrammar;
		return new Builder(m::mock('Database\ConnectionInterface'), $grammar);
	}


	protected function getSQLiteBuilder()
	{
		$grammar = new Database\Query\Grammars\SQLiteGrammar;
		return new Builder(m::mock('Database\ConnectionInterface'), $grammar);
	}


	protected function getSqlServerBuilder()
	{
		$grammar = new Database\Query\Grammars\SqlServerGrammar;
		return new Builder(m::mock('Database\ConnectionInterface'), $grammar);
	}

}
