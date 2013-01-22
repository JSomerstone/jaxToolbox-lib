<?php
namespace jaxToolbox\lib;

abstract class Curlifier
{
    static $defaults = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_HEADER => 1,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_FORBID_REUSE => 1,
        //CURLOPT_VERBOSE => 1,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_FOLLOWLOCATION => true
    );

    static $userAgents = array(
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

    static $lastUrl = 'http://localhost/';

    public static function request(
        $url = 'http://localhost',
        $get = array(),
        $post = array(),
        $cookie = array(),
        $referer = null
    )
    {
        $curlHandler = curl_init();

        $url = sprintf(
                "$url%s%s",
                empty($get) ? '' : '?',
                self::curlify($get)
        );
        $settings = array(
            CURLOPT_URL             => $url,
            CURLOPT_POST            => empty($post) ? 0 : 1,
            CURLOPT_POSTFIELDS      => self::curlify($post),
            CURLOPT_REFERER         => $referer ?: self::$lastUrl,
            CURLOPT_USERAGENT       => self::$userAgents[rand(0,count(self::$userAgents)-1)]
        );

        if (!empty($cookie))
            $settings[CURLOPT_COOKIE] = self::curlify($cookie);

        curl_setopt_array($curlHandler, $settings + self::$defaults);

        //$output = curl_exec($curlHandler);
        $response = curl_exec($curlHandler);
        $error = curl_error($curlHandler);
        $result = array( 'header' => '',
                         'body' => '',
                         'curl_error' => '',
                         'http_code' => '',
                         'last_url' => '');
        if ( $error != "" )
        {
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($curlHandler, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($curlHandler, CURLINFO_EFFECTIVE_URL);
        self::$lastUrl = $result['last_url'];
        return $result;
    }

    public static function getRedirect($url)
    {

    }

    private static function curlify($settings)
    {
        $curlified = '';
        foreach($settings as $key => $value)
        {
            $curlified .= $key.'='.$value.'&';
        }
        return rtrim($curlified, '&');
    }

}