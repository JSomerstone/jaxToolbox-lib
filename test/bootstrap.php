<?php
include_once __DIR__ . '/Autoloader.php';

function D()
{
    ob_flush();

    echo "\n------DEBUG-START------\n";
    foreach(func_get_args() as $debug)
    {
        var_dump($debug);
    }
     echo "\n------DEBUG-END------\n";

    ob_start();
}

function DE()
{
    call_user_func_array('D', func_get_args());
    exit(1);
}