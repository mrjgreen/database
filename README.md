## Database

[![Build Status](https://travis-ci.org/mrjgreen/database.svg?branch=master)](https://travis-ci.org/mrjgreen/database)
[![Coverage Status](https://img.shields.io/coveralls/mrjgreen/database.svg)](https://coveralls.io/r/mrjgreen/database)

The Database component is a framework agnostic PHP database abstraction layer, providing an expressive query builder. It currently supports MySQL, Postgres, SQL Server, and SQLite.

Features:

* Simple CRUD functions
* Support for Insert Ignore / Replace
* Joins
* Sub Queries
* Nested Queries
* Bulk Insersts
* Database Connection Resolver

The component is based on Laravel's Illuminate\Database and has very familiar syntax. The core Query Builder is mostly compatible. The main alterations are to the composition of the objects, and most significantly the creation and resolution of connections within the ConnectionFactory and ConnectionResolver classes.

### Usage Instructions

First, create a new "Factory" instance.

```PHP
$factory = new \Database\Connectors\ConnectionFactory();

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
	   ->where('price','>', 100)
	   ->get();
```
## Documentation

### Table of Contents

 - [**Connection**](#connection)
    - [SQLite](#sqlite)
 - [**Raw Queries**](#raw-queries)
    - [Query Shortcuts](#query-shortcuts)
 - [**Query Builder**](#query-builder)
 - [Selects](#selects)
    - [Find By ID](#find-by-id)
    - [Select Columns](#select-columns)
    - [Limit and Offset](#limit-and-offset)
    - [Sub Selects](#sub-selects)
 - [Insert](#insert)
    - [Batch Insert](#batch-insert)
 - [Update](#update)
 - [Delete](#delete)
 - [Raw Expressions](#raw-expressions)
 - [Get SQL](#get-sql-query-and-bindings)
 - [Raw PDO Instance](#raw-pdo-instance)
___

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

#### Find By ID
```PHP
$row = $connection->table('users')->find(6);
```

The query above assumes your table's primary key is `'id'`. You can specify your primary key:
```PHP
$connection->table('users')->find(3, 'user_id');
```

#### Select Columns
```PHP
$rows = $connection->table('users')->select('name')->addSelect('age', 'dob')->get();
```

#### Limit and Offset
```PHP
$connection->table('users')->offset(100)->limit(10);
```

#### Sub Selects

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

### Insert
```PHP
$data = array(
    'username' = 'jsmith',
    'name' = 'John Smith'
);
$insertIds = $connection->table('users')->insert($data);
```

`insert()` method returns the insert id.

#### Batch Insert
The query builder will intellegently handle multiple insert rows:
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
$insertIds = $connection->table('users')->insert($data);
```

### Update
```PHP
$data = array(
    'username' = 'jsmith123',
    'name' = 'John Smith'
);

$connection->table('users')->where('id', 123)->update($data);
```

### Delete
```PHP
$connection->table('users')->where('last_active', '>', 12)->delete();
```
Will delete all the rows where id is greater than 5.

### Raw Expressions

Wrap raw queries with `$connection->raw()` to bypass query parameter binding. NB use with caution - no sanitisation will take place.
```PHP
$connection->table('users')
            ->select($connection->raw('DATE(activity_time) as activity_date'))
            ->where('user', '=', 123)
            ->get();
```

### Get SQL Query and Bindings
Sometimes you may need to get the query string, its possible.
```PHP
$query = $connection->table('users')->find(1);
$query->toSql();
// SELECT * FROM users where `id` = ?

$queryObj->getBindings();
// array(1)
```
   

### Raw PDO Instance
You can access the PDO object in use on any connection:

```PHP
$connection->getPdo();
```
