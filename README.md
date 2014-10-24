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
