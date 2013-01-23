<?php
namespace jaxToolbox\lib;

class Curlifier
{
    private $curlHandler;
    private $defaults = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_HEADER => 1,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_FORBID_REUSE => 1,
        //CURLOPT_VERBOSE => 1,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_FOLLOWLOCATION => true
    );

    private $userAgent = '';
    private $userAgents = array(
        'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
        'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))',
        'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1309.0 Safari/537.17',
        'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.15 (KHTML, like Gecko) Chrome/24.0.1295.0 Safari/537.15',
        'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.14 (KHTML, like Gecko) Chrome/24.0.1292.0 Safari/537.14',
        'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10',
        'Opera/12.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.02',
        'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00',
        'Opera/9.80 (Windows NT 6.1; WOW64; U; pt) Presto/2.10.229 Version/11.62',
        'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52',
        'Mozilla/5.0 (X11; Linux i686; rv:7.0a1) Gecko/20110604 SeaMonkey/2.2a1pre',
        'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; ja-jp) AppleWebKit/419 (KHTML, like Gecko) Shiira/1.2.3 Safari/125',
    );

    private $lastHeader = '';
    private $lastBody = '';
    private $lastHttpdCode = '';
    private $lastUrl = 'http://localhost/';
    private $curlError = '';

    private $cookies;

    private $nextUrl;
    private $nextPost = array();
    private $nextGet = array();

    public function __construct()
    {
        $this->curlHandler = curl_init();
    }

    public function setUrl($url)
    {
        $this->nextUrl = $url;
        return $this;
    }

    public function setPost($post = array())
    {
        $this->nextPost = $post;
        return $this;
    }

    public function setGet($get = array())
    {
        $this->nextGet = $get;
        return $this;
    }

    public function setReferer($url)
    {
        $this->lastUrl = $url;
        return $this;
    }

    public function request(
        $url = null,
        $get = null,
        $post = null,
        $cookie = null,
        $referer = null
    )
    {
        if (!is_null($url))
            $this->setUrl($url);

        if (!is_null($get))
            $this->setGet($get);

        if (!is_null($post))
            $this->setPost($post);

        if (!is_null($cookie))
            $this->setUrl($cookie);

        $url = sprintf(
            "$this->nextUrl%s%s",
            empty($this->nextGet) ? '' : '?',
            self::curlify($this->nextGet)
        );
        $settings = array(
            CURLOPT_URL             => $url,
            CURLOPT_POST            => empty($this->nextPost) ? 0 : 1,
            CURLOPT_POSTFIELDS      => self::curlify($this->nextPost),
            CURLOPT_REFERER         => $referer ?: $this->lastUrl,
            CURLOPT_USERAGENT       => $this->userAgent ?: $this->userAgents[
                rand(0,count($this->userAgents)-1)
            ]
        );

        if (!empty($this->cookies))
            $settings[CURLOPT_COOKIE] = self::curlify($this->cookies);

        curl_setopt_array($this->curlHandler, $settings + $this->defaults);

        $response = curl_exec($this->curlHandler);
        $error = curl_error($this->curlHandler);
        if ( $error != "" )
        {
            throw new CurlifierException($error);
        }

        $headerSize = curl_getinfo($this->curlHandler, CURLINFO_HEADER_SIZE);
        $this->lastHeader = substr($response, 0, $headerSize);
        $this->lastBody = substr( $response, $headerSize );
        $this->lastHttpdCode = curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE);
        $this->lastUrl = curl_getinfo($this->curlHandler, CURLINFO_EFFECTIVE_URL);

        $this->getCookieFromHeader();
        return $this;
    }

    private function getCookieFromHeader()
    {
        if (empty($this->lastHeader))
            return;
        preg_match('|SECUREWEBSTAGE11SESSION=[a-zA-Z0-9\/_]+|', $this->lastHeader, $matches);
    }

    public function setCookies($listOfCookies)
    {
       $this->cookies = array();
        foreach($listOfCookies as $name => $value)
        {
            $this->addCookie($name, $value);
        }
    }

    public function setCookie($name, $value)
    {
        $this->cookies = array($name => $value);
        return $this;
    }

    public function addCookie($name, $value)
    {
        $this->cookies[$name] = $value;
        return $this;
    }

    public function removeCookie($name)
    {
        unset($this->cookies[$name]);
        return $this;
    }

    public function getHeader()
    {
        return $this->lastHeader;
    }

    public function getBody()
    {
        return $this->lastBody;
    }

    public function getBodyMatches($regexp)
    {
        preg_match($regexp, $this->lastBody, $matches);
        return $matches;
    }

    public function bodyMatchesExpression($regexp)
    {
        return preg_match($regexp, $this->lastBody);
    }

    public function getHttpCode()
    {
        return $this->lastHttpdCode;
    }

    private static function curlify($settings = array())
    {
        $curlified = '';
        foreach($settings as $key => $value)
        {
            $curlified .= $key.'='.$value.'&';
        }
        return rtrim($curlified, '&');
    }

}

class CurlifierException extends \Exception {}