<?php
/**
 * Simple mirror class that returns the header, get and post parameters it is called with
 */
echo json_encode(array(
    'header' => $_SERVER,
    'get' => $_GET,
    'post' => $_POST,
    'cookie' => $_COOKIE
));
