<?php
return array(
    array(
        'host'      => 'localhost',
        'driver'    => 'mysql',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'options'   => array(
            PDO::ATTR_EMULATE_PREPARES => false
        )
    ),
    array(
        'host'      => '127.0.0.1',
        'driver'    => 'mysql',
        'username'  => 'root',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'options'   => array(
            PDO::ATTR_EMULATE_PREPARES => false
        )
    )
);
