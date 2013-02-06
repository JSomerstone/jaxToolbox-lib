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
        CURLOPT_VERBOSE => 0,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_FOLLOWLOCATION => 0
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

    public function setVerbose($bool = true)
    {
        $this->defaults[CURLOPT_VERBOSE] = $bool ? 1 : 0;
        return $this;
    }

    public function setFollowRedirect($bool = true)
    {
        $this->defaults[CURLOPT_FOLLOWLOCATION] = $bool ? 1 : 0;
        return $this;
    }

    public function setHeaderIp($ipAddress)
    {
        $this->defaults[CURLOPT_HTTPHEADER] = array(
            "REMOTE_ADDR: $ipAddress",
            "HTTP_X_FORWARDED_FOR: $ipAddress",
            "HTTP_X_FORWARDED: $ipAddress",
            "HTTP_CLIENT_IP: $ipAddress",
        );
        return $this;
    }

    /**
     * Make a curl-request
     *
     * All parameters are optional, the URL must be given either as parameter or via ->setUrl()
     * All settings from $parameters will override those set via ->set*() -functions
     *
     * $parameters array can hold following indexes:
     *      url - The url to send request to
     *      get - Associative array with GET-parameters
     *      post - Associative array with POST-parameters
     *      cookie - Associative array with Cookie names and values
     *      referer - The URL to send as "referer" in the request
     *      userAgent - User agent -string
     *
     * @param array $parameters optional parameters to use on this request only
     * @return \jaxToolbox\lib\Curlifier
     * @throws CurlifierException If an curl-error is encoutered
     */
    public function request($parameters = array())
    {
        $url = isset($parameters['url']) ? $parameters['url'] : $this->nextUrl;
        $get = isset($parameters['get']) ? $parameters['get'] : $this->nextGet;
        $post = isset($parameters['post']) ? $parameters['post'] : $this->nextPost;
        $cookies = isset($parameters['cookie'])
            ? array_merge($this->cookies, $parameters['cookie'])
            : $this->cookies;
        $referer = isset($parameters['referer']) ? $parameters['referer'] : $this->lastUrl;
        $userAgent = isset($parameters['userAgent']) ? $parameters['userAgent'] : $this->userAgent;

        if (empty($url))
        {
            throw new CurlifierException('Unable to make request to empty URL');
        }

        $url = sprintf(
            "$url%s%s",
            empty($get) ? '' : '?',
            self::curlify($get)
        );

        $settings = array(
            CURLOPT_URL             => $url,
            CURLOPT_REFERER         => $referer,
            CURLOPT_USERAGENT       => $userAgent ?: $this->userAgents[
                rand(0,count($this->userAgents)-1)
            ]
        );

        $settings[CURLOPT_POST] = empty($post);
        $settings[CURLOPT_POSTFIELDS] = self::curlify($post);

        if (!empty($cookies))
            $settings[CURLOPT_COOKIE] = self::curlify($cookies);

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
        // TODO: parse "Set-Cookie: "-headers and set them
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

    public function getHeaderMatches($regexp)
    {
        preg_match($regexp, $this->lastHeader, $matches);
        return $matches;
    }

    public function headerMatchesExpression($regexp)
    {
        return preg_match($regexp, $this->lastHeader);
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