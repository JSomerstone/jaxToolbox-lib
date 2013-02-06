<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__DIR__).'/source/');

spl_autoload_register(function($class) {

    $class = str_replace('jaxToolbox\\', '\\', $class);
    $class = ltrim($class, '\\');

    include_once str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});