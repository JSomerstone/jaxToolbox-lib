jaxToolbox-lib
==============

Collection of helpful PHP-classes.

jaxToolbox/lib/Curlifier
========================
cURL wrapper with neat command-chaining possibilities.

Example of simple get-request
```php
$crl = new \jaxToolbox\lib\Curlifier();
$response = $crl->setUrl('http://google.com')
	->request()
	->getBody();
```

Introduction
------------
Curlifier is a object oriented wrapper for making cURL-requests and receiving the response. It supports method chaining, post- and get-requests, cookies and custom user-agents. It also provides interface to validate responses header and body against given regexpes.
Examples
--------
_Work in progress_

Api
---
**Request**
There is multiple ways to perform requests with Curlifier. You can either set all required parameters with setters (see below) or pass them as parameter to the request()-method. Or you can mix the two ways.

**request(array $parameters = array())**
Performs the actual cURL-request and stores info about the response. Parameters to request() are optional but user _must_ provide URL either directly to request() or via setUrl()-method. Request will return the Curlifier-object so the actions after request() can be chained. This method will throw **CurlifierException** if URL is not provided.

Possible keys in $parameters are:
- url - The url to send request to
- get - Associative array with GET-parameters
- post - Associative array with POST-parameters
- cookie - Associative array with Cookie names and values
- referer - The URL to send as "referer" in the request
- userAgent - User agent -string

**Request with parameters passed to request()-method**:
```php
$curlifier->request(array(
    'url' => 'http://localhost/pageApp.php',
    'get' => array('page' => '1'),
    'cookie' => array('MYCOOKIE' => 'RANDOMVALUE')
));
```
Note that parameters passed to request() will only affect that request - the next request will not have them.
**Request with setters**:
```php
$curlifier->setUrl('http://localhost/pageApp.php')
    ->setGet(array('page' => '1'))
    ->setCookie(array('MYCOOKIE' => 'RANDOMVALUE'))
    ->request();
));
```
Note that parameters set with setters will affect every request onwards.

Setters
-------
Setters reset the settings/parameters of Curlifier. These settings will affect _every_ request where parameters passed to request() will only affect that request. All setters return the Curlifier-object so they can be chained.

**setCookie(string $name, string $value)**

Replaces current cookie(s) with given.
```php
$curlifier->setCookie('MYCOOKIE', 'SOMEVERYSECRETVALUE');
```

**setCookies(array $listOfCookies)**

Replaces current cookies with given cookies. Cookies must be given as associative array:
```php
array( 'nameOfCookie' => 'valueOfCookie')
```

```php
$curlifier->setCookies(array(
    'MYCOOKIE' => 'SOMEVERYSECRETVALUE',
    'OTHERCOOKIE' => 'VALUEOFTHAT',
));
``
**addCookie(string $name, string $value)**

Add new cookie to existing ones.
```php
$curlifier->addCookie('OTHERCOOKIE', 'SOMEVERYSECRETVALUE');
```

**setFollowRedirect(boolean $bool = true)**

cURL can be set to automatically follow HTTP-redirections. If setFollowRedirect is not set the Curlifier will **not** follow redirections automatically.
To enable redirect following:
```php
$curlifier->setFollowRedirect();
//or
$curlifier->setFollowRedirect(true);
```
To disable redirect following:
```php
$curlifier->setFollowRedirect(false);
```

**setGet(array $get = array())**

Set the GET-parameters to pass on each request. Will override any previous parameters set. If called without any parameters (or an empty array) will remove all GET-parameters.
```php
$curlifier->setGet(array(
    'page' => '2',
    'query' => 'foo',
));
```

**setPost(array $get = array())**

Set the POST-parameters to pass on each request. Will override any previous parameters set. If called without any parameters (or an empty array) will remove all POST-parameters.
```php
$curlifier->setPOST(array(
    'id' => '2',
    'name' => 'foo',
));
```

**setReferer(string $url)**

Set the request header "HTTP_REFERER" to given value.
```php
$curlifier->setReferer('http://localhost/');
```

**setUrl(string $url)**

Sets the URL to where each request will be send. The url-setting is only mandatory parameter to set in order to call request()-method.
```php
$curlifier->setUrl('http://localhost/example.php');
```

**setUserAgent(string $userAgent)**

Sets the request header "HTTP_USER_AGENT" to given value. If user agent is not specified, Curlifier will use "cURL" as user agent.
```php
$curlifier->setUserAgent('Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))');
```

**setRandomUserAgent()**

Curlifier has set of pre-defined user agents. Calling this function will randomly pick one of them and set the request header "HTTP_USER_AGENT". If user agent is not specified, Curlifier will use "cURL" as user agent.
```php
$curlifier->setRandomUserAgent();
```

**setVerbose(boolean $bool = true)**

Curlifier can set the cURL to output the request process into scripts output. If not specified Curlifier will **not** output the request process.
To enable verbose mode:
``php
$curlifier->setVerbose();
//or
$curlifier->setVerbose(true);
```
To disable verbose mode:
```php
$curlifier->setVerbose(false);
```

Getters
-------
Getters should be called **after** the _request()_-method.

**getBody()**

Returns the body of last response as **string**.
```php
$responseBody = $curlifier->request()->getBody();
```

**getHeader()**

Returns the header of last response as **string**.
```php
$responseHeader = $curlifier->request()->getHeader();
```

**getHttpCode()**

Returns the HTTP_CODE of the last response.
```php
$requestOk = ($curlifier->request()->getHttpCode() === 200);
```

Regexp checks
-------------
Curlifier provides couple of ways to directly examine the response with regular expressions.

**bodyMatchesExpression(string $regexp)**

Returns **1** if responses body matches given regular expression. **0** if it doesn't and **false** if there were an error (invalid regexp) during check.
```php
$hasEmails = $curlifier->request()->bodyMatchesExpression('/[a-z0-9_]+@example.com/');
```

**getBodyMatches(string $regexp)**

Returns matches from body to given regular expression as **array**. See [preg_match](http://php.net/preg_match) for more details.
```php
$emails = $curlifier->request()->getBodyMatches('/[a-z0-9_]+@example.com/');
```

**headerMatchesExpression(string $regexp)**

Returns **1** if responses header matches given regular expression. **0** if it doesn't and **false** if there were an error (invalid regexp) during check.
```php
$redirected = $curlifier->request()->headerMatchesExpression('|Location: (http(s)?:/)?/[/a-z0-9_]+.html|');
```

**getHeaderMatches(string $regexp)**

Returns matches from header to given regular expression as **array**. See [preg_match](http://php.net/preg_match) for more details.
```php
$redirUrl = $curlifier->request()->getHeaderMatches('|http://localhost/content/fi/[0-9/]+.html|');
```

Roadmap
-------

- Multi-cookie support - **Done**
- Support for "cookie jar"
- Auto-parsing responses "Set-cookie" -headers and setting cookie accordingly
- Getting PHP-array from JSON-response
- Getting PHP-array from XML-response


jaxToolbox/lib/StringPermutator
===============================

Class to iterate all possible permutations of string with given character-pool and length
