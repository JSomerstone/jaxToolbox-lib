jaxToolbox-lib
==============

Collection of helpful PHP-classes. 

jaxToolbox/lib/Curlifier
========================
cURL wrapper with neat command-chaining possibilities.

Example of simple get-request

$crl = new \jaxToolbox\lib\Curlifier();
$response = $crl->setUrl('http://google.com')
	->request()
	->getBody();


jaxToolbox/lib/StringPermutator
===============================

Class to iterate all possible permutations of string with given character-pool and length
