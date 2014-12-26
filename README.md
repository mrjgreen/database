## Database

[![Build Status](https://travis-ci.org/mrjgreen/database.svg?branch=master)](https://travis-ci.org/mrjgreen/database)
[![Coverage Status](https://img.shields.io/coveralls/mrjgreen/database.svg)](https://coveralls.io/r/mrjgreen/database)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4a157949-f3dd-46a4-958c-b4c02ec836b3/mini.png)](https://insight.sensiolabs.com/projects/4a157949-f3dd-46a4-958c-b4c02ec836b3)

The Database component is a framework agnostic PHP database abstraction layer, providing an expressive query builder. It currently supports MySQL, Postgres, SQL Server, and SQLite.

Features:

* Simple CRUD functions
* Support for Insert Ignore / Replace
* Support for Insert On Duplicate Key Update
* Support for direct `INSERT INTO ... SELECT * FROM` queries
* Buffered inserts from Traversable/Iterator interfaces
* Joins
* Sub Queries
* Nested Queries
* Bulk Inserts
* Database Connection Resolver

The component is based on Laravel's Illuminate\Database and has very familiar syntax. The core Query Builder is mostly compatible. The main alterations are to the composition of the objects, and most significantly the creation and resolution of connections within the ConnectionFactory and ConnectionResolver classes.

### Basic Example

First, create a new "ConnectionFactory" instance.

```PHP
$factory = new \Database\Connectors\ConnectionFactory();

$connection = $factory->make(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',

    // Don't connect until we execute our first query
    'lazy'      => true,

    // Set PDO attributes after connection
    'options' => array(
        PDO::MYSQL_ATTR_LOCAL_INFILE    => true,
        PDO::ATTR_EMULATE_PREPARES      => true,
    )
));

$connection->query("SELECT id, username FROM customers");
```
## Documentation

### Table of Contents

 - [**Connection**](#connection)
    - [MySQL](#mysql)
    - [SQLite](#sqlite)
    - [Default Connection Options](#default-connection-options)
 - [**Connection Resolver**](#connection-resolver)
 - [**Raw Queries**](#raw-queries)
    - [Query Shortcuts](#query-shortcuts)
 - [**Query Builder**](#query-builder)
 - [Selects](#selects)
    - [Get All](#get-all)
    - [Get First Row](#get-first-row)
    - [Find By ID](#find-by-id)
    - [Select Columns](#select-columns)
    - [Limit and Offset](#limit-and-offset)
    - [Where](#where)
    - [Grouped Where](#grouped-where)
    - [Group By, Order By and Having](#group-by-order-by-and-having)
    - [Joins](#joins)
    - [Sub Selects](#sub-selects)
 - [Insert](#insert)
    - [Insert Ignore](#insert-ignore)
    - [Replace](#replace)
    - [Batch Insert](#batch-insert)
    - [On Duplicate Key Update](#on-duplicate-key-update)
    - [Insert Select](#insert-select)
    - [Buffered Iterator Insert](#buffered-iterator-insert)
 - [Update](#update)
 - [Delete](#delete)
 - [Raw Expressions](#raw-expressions)
 - [Get SQL](#get-sql-query-and-bindings)
 - [Raw PDO Instance](#raw-pdo-instance)


## Connection
The Database component supports MySQL, SQLite, SqlServer and PostgreSQL drivers. You can specify the driver during connection and the associated configuration when creating a new connection. You can also create multiple connections, but you can use alias for only one connection at a time.;
```PHP
$factory = new \Database\Connectors\ConnectionFactory();
```

### MySQL
```PHP
$connection = $factory->make(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
));

$connection->fetchAll("SELECT id, username FROM customers"); 

$connection->table('customers')
	   ->find(12);
	   
$connection->table('customers')
	   ->join('products', 'customer.id', '=', 'customer_id')
	   ->where('favourites', '=', 1)
	   ->get();
```


### SQLite 
```PHP
$connection = $factory->make(array(
    'driver'    => 'sqlite',
    'database' => '/path/to/sqlite.db',
));
```

###Default Connection Options
By default the following PDO attributes will be set on connection. You can override these or add to them in the
`options` array parameter in the connection config.

```PHP
PDO::ATTR_CASE              => PDO::CASE_NATURAL,
PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
PDO::ATTR_STRINGIFY_FETCHES => false,
PDO::ATTR_EMULATE_PREPARES  => false,
```

## Connection Resolver
Many complex applications may need more than one database connection. You can create a set of named connections inside the connection
resolver, and reference them by name within in your application.

```PHP

$resolver = new Database\ConnectionResolver(array(
    'local' => array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'username'  => 'root',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'archive' => array(
        'driver'    => 'mysql',
        'host'      => '1.2.3.456',
        'username'  => 'root',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
));

$dbLocal = $resolver->connection('local');

// Use it
$dbLocal->table('users')->get();


$dbArchive = $resolver->connection('archive');
// Etc...
```

If you request a connection that you have used previously in your application, the connection resolver will return the same connection, rather than create a new one.


You can set a default connection after creating the resolver, so you don't have to specify the connection name throughout your application.
```PHP
$resolver->setDefaultConnection('local');

// Returns the `local` connection
$resolver->connection();
```

##Raw Queries
Perform a query, with bindings and return the PDOStatement object
```PHP
$statement = $connection->query('SELECT * FROM users WHERE name = ?', array('John Smith'));

// PDOStatement
$statement->rowCount();
$statement->fetchAll();
```

###Query Shortcuts
```PHP
$firstRow = $connection->fetch('SELECT * FROM users WHERE name = ?', array('John Smith'));

$allRows = $connection->fetchAll('SELECT * FROM users WHERE name = ?', array('John Smith'));

$firstColumnFirstRow = $connection->fetchOne('SELECT COUNT(*) FROM users WHERE name = ?', array('John Smith'));
```


##Query Builder

###Selects

####Get PDOStatement
If you intend to iterate through the rows, it may be more efficient to get the PDOStatement
```PHP
$rows = $connection->table('users')->query();
```

####Get All
```PHP
$rows = $connection->table('users')->get();
```

####Get First Row
```PHP
$row = $connection->table('users')->first();
```

####Find By ID
```PHP
$row = $connection->table('users')->find(6);
```

The query above assumes your table's primary key is `'id'` and you want to retreive all columns. You can specify the columns you want to fetch, and your primary key:
```PHP
$connection->table('users')->find(3, array('user_id', 'name', 'email'), 'user_id');
```

####Select Columns
```PHP
$rows = $connection->table('users')->select('name')->addSelect('age', 'dob')->get();
```

####Limit and Offset
```PHP
$connection->table('users')->offset(100)->limit(10);
```

####Where

```PHP
$connection->table('user')
    ->where('username', '=', 'jsmith')
    ->whereNotIn('age', array(10,20,30))
    ->orWhere('type', '=', 'admin')
    ->orWhereNot('name', 'LIKE', '%Smith%')
    ->get();
```

#####Grouped Where

```PHP
$connection->table('users')
            ->where('age', '>', 10)
            ->orWhere(function($subWhere)
                {
                    $subWhere
                        ->where('animal', '=', 'dog')
                        ->where('age', '>', 1)
                });

SELECT * FROM `users` WHERE `age` > 10 or (`age` > 1 and `animal` = 'dog')`.
```

####Group By, Order By and Having
```PHP
$users = $connection->table('users')
                    ->orderBy('name', 'desc')
                    ->groupBy('count')
                    ->having('count', '>', 100)
                    ->get();
```


#### Joins
```PHP
$connection->table('users')
    ->join('products', 'user_id', '=', 'users.id')
    ->get();
/*
    ->leftJoin()
    ->rightJoin()
*/
```

##### Multiple Join Criteria
If you need more than one criterion to join a table then you can pass a closure as second parameter.

```PHP
->join('products', function($table)
    {
        $table->on('users.id', '=', 'products.user_id');
        $table->on('products.price', '>', 'users.max_price');
    })
```

####Sub Selects

```PHP
$query = $connection->table('users')
            ->selectSub(function($subQuery){
            	$subQuery
            	->table('customer')
            	->select('name')
            	->where('id', '=', 'users.id');
            }, 'tmp');
```

This will produce a query like this:

    SELECT (SELECT `name` FROM `customer` WHERE `id` = users.id) as `tmp` FROM `users`

####Aggregates

#####Count
```PHP
$count = $connection->table('users')->count();
```

#####Min
```PHP
$count = $connection->table('users')->min('age');
```

#####Max
```PHP
$count = $connection->table('users')->max('age');
```

#####Average
```PHP
$count = $connection->table('users')->avg('age');
```

#####Sum
```PHP
$count = $connection->table('users')->sum('age');
```

###Insert
```PHP
$data = array(
    'username' = 'jsmith',
    'name' = 'John Smith'
);
$connection->table('users')->insert($data);
// Returns PDOStatement

`->insertGetId($data)` method returns the insert id instead of a PDOStatement
```

###Insert Ignore
Ignore errors from any rows inserted with a duplicate unique key
```PHP
$data = array(
    'username' = 'jsmith',
    'name' = 'John Smith'
);
$connection->table('users')->insertIgnore($data);
```

###Replace
Replace existing rows with a matching unique key
```PHP
$data = array(
    'username' = 'jsmith',
    'name' = 'John Smith'
);
$connection->table('users')->replace($data);
```

####Batch Insert
The query builder will intelligently handle multiple insert rows:
```PHP
$data = array(
	array(
	    'username' = 'jsmith',
	    'name' = 'John Smith'
	),
	array(
	    'username' = 'jbloggs',
	    'name' = 'Joe Bloggs'
	),
);
$connection->table('users')->insert($data);
```

You can also pass bulk inserts to replace() and insertIgnore()

###On Duplicate Key Update
```PHP
$data = array(
    'username' = 'jsmith',
    'name' = 'John Smith'
);

$now = $connection->raw('NOW()');

$connection->table('users')->insertUpdate(
    array('username' => 'jsmith', 'active' => $now), // Insert this data
    array('active' => $now)                          // Or partially update the row if it exists
);

//insertOnDuplicateKeyUpdate() is an alias of insertUpdate
```

####Insert Select
$connection->table('users')->insertSelect(function($select){
    $select->from('admin')
            ->select('name', 'email')
            ->where('status', '=', 1);

}, array('name','email'));

`insertIgnoreSelect` and `replaceSelect` methods are supported for the MySQL grammar driver.

####Buffered Iterator Insert
If you have a large data set you can insert in batches of a chosen size (insert ignore/replace/on duplicate key update supported).

This is especially useful if you want to select large data-sets from one server and insert into another.

~~~PHP
$pdoStatement = $mainServer->table('users')->query(); // Returns a PDOStatement (which implements the `Traversable` interface)

// Will be inserted in batches of 1000 as it reads from the rowset iterator.
$backupServer->table('users')->buffer(1000)->insertIgnore($pdoStatement);
~~~

###Update
```PHP
$data = array(
    'username' = 'jsmith123',
    'name' = 'John Smith'
);

$connection->table('users')->where('id', 123)->update($data);
```

###Delete
```PHP
$connection->table('users')->where('last_active', '>', 12)->delete();
```

Will delete all the rows where id is greater than 5.

###Raw Expressions

Wrap raw queries with `$connection->raw()` to bypass query parameter binding. NB use with caution - no sanitisation will take place.
```PHP
$connection->table('users')
            ->select($connection->raw('DATE(activity_time) as activity_date'))
            ->where('user', '=', 123)
            ->get();
```

###Get SQL Query and Bindings

```PHP
$query = $connection->table('users')->find(1)->toSql();
$query->toSql();
// SELECT * FROM users where `id` = ?

$query->getBindings();
// array(1)
```
   

###Raw PDO Instance
```PHP
$connection->getPdo();
```
