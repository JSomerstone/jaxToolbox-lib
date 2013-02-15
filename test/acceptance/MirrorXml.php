<?php
header('Content-type: application/xml');
/**
 * Simple mirror class that returns the header, get and post parameters it is called with
 */
$xml = new SimpleXMLElement('<request/>');
$request = array(
    'header' => $_SERVER,
    'get' => $_GET,
    'post' => $_POST,
    'cookie' => $_COOKIE
);



arrayToXml($request,$xml);

//saving generated xml file
print $xml->asXML();


// function defination to convert array to xml
function arrayToXml($array, &$xml) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml->addChild("$key");
                arrayToXml($value, $subnode);
            }
            else{
                arrayToXml($value, $xml);
            }
        }
        else {
            $xml->addChild("$key","$value");
        }
    }
}