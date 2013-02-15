<?php
namespace test\acceptance\lib;

class CurlifierTest extends \test\acceptance\AcceptanceTestCase
{
    /**
     * @var \jaxToolbox\lib\Curlifier
     */
    protected $curlifier;
    protected $header;
    protected $post;
    protected $get;
    protected $cookie;
    protected $requestRead = false;

    public function setUp()
    {
        $this->curlifier = new \jaxToolbox\lib\Curlifier();

        $url = 'http://localhost/Mirror.php';

        $this->curlifier->setUrl($url);
    }

    public function teardown()
    {
        $this->header = array();
        $this->post = array();
        $this->get = array();
        $this->cookie = array();
        $this->requestRead = false;
    }

    public function testEmptyRequest()
    {
       $this->curlifier->request();
       $this->assertHttpSuccess()
            ->assertGetEmpty()
            ->assertPostEmpty()
            ->assertCookieEmpty()
            ->assertArrayHasKeys(
                array(
                    'HTTP_USER_AGENT',
                    'HTTP_REFERER',
                    'REQUEST_METHOD',
                    'REQUEST_URI',
                ),
                $this->header
            );
    }

    /**
     * @test
     */
    public function testSetGet()
    {
        $input = array('one' => 1);
        $this->curlifier->setGet($input);
        $this->curlifier->request();
        $this
            ->assertHttpSuccess()
            ->assertGetNotEmpty()
            ->assertPostEmpty();
    }

    /**
     * @test
     */
    public function testGetRequest()
    {
        $input = array('fuu' => 'bar');
        $this->curlifier->request(array(
            'get' => $input
        ));
        $this
            ->assertHttpSuccess()
            ->assertGetHasValues($input)
            ->assertPostEmpty();
    }

    /**
     * @test
     */
    public function testMixingGetTypes()
    {
        $setGet = array('fuu' => 'bar');
        $requestGet = array('one' => '1');
        $expectedGet = array(
            'fuu' => 'bar',
            'one' => '1'
        );

        $this->curlifier
            ->setGet($setGet)
            ->request(array(
                'get' => $requestGet
            ));

        $this
            ->assertHttpSuccess()
            ->assertGetHasValues($expectedGet)
            ->assertPostEmpty();
    }

    /**
     * @test
     */
    public function testMixingGetTypesWithConflicting()
    {
        $setGet = array('source' => 'setter');
        $requestGet = array('source' => 'request');
        $expectedGet = array(
            'source' => 'request'
        );

        $this->curlifier
            ->setGet($setGet)
            ->request(array(
                'get' => $requestGet
            ));

        $this
            ->assertHttpSuccess()
            ->assertGetHasValues($expectedGet)
            ->assertPostEmpty();
    }

    /**
     * @test
     */
    public function testSetPost()
    {
        $input = array('one' => 1);
        $this->curlifier->setPost($input);
        $this->curlifier->request();
        $this
            ->assertHttpSuccess()
            ->assertGetEmpty()
            ->assertPostHasValues($input);

        $this->teardown(); //Clear results
        $this->curlifier->request();
        //Post set by setPost() should be present in the next
        $this->assertPostNotEmpty();
    }

    /**
     * @test
     */
    public function testPostRequest()
    {
        $input = array('one' => 1);
        $this->curlifier->request(array(
            'post' => $input
        ));
        $this
            ->assertHttpSuccess()
            ->assertGetEmpty()
            ->assertPostHasValues($input);

        $this->teardown(); //Clear results
        $this->curlifier->request();
        //Post set in request should not be present in the next
        $this->assertPostEmpty();
    }

    public function testMixingPostTypes()
    {
        $setPost = array('fuu' => 'bar');
        $requestPost = array('one' => '1');
        $expectedPost = array(
            'fuu' => 'bar',
            'one' => '1'
        );

        $this->curlifier
            ->setPost($setPost)
            ->request(array(
                'post' => $requestPost
            ));

        $this
            ->assertHttpSuccess()
            ->assertPostHasValues($expectedPost);
    }

    public function testMixingPostTypesWithConflicting()
    {
        $setPost = array('source' => 'setter');
        $requestPost = array('source' => 'request');
        $expectedPost = array(
            'source' => 'request'
        );

        $this->curlifier
            ->setPost($setPost)
            ->request(array(
                'post' => $requestPost
            ));

        $this
            ->assertHttpSuccess()
            ->assertPostHasValues($expectedPost);
    }

    public function testSetReferer()
    {
        $expectedReferer = 'http://google.com';
        $this->curlifier->setReferer($expectedReferer);
        $this->curlifier->request();

        $this
            ->assertHttpSuccess()
            ->assertHeaderReferer($expectedReferer);
    }

    public function testRequestWithReferer()
    {
        $expectedReferer = 'http://google.com';
        $this->curlifier->request(array(
            'referer' => $expectedReferer
        ));

        $this
            ->assertHttpSuccess()
            ->assertHeaderReferer($expectedReferer);
    }

    public function testSetUserAgent()
    {
        $userAgent = 'FakeUserAgent 1.2';
        $this->curlifier
            ->setUserAgent($userAgent)
            ->request();

        $this->assertHttpSuccess()
            ->assertUserAgent($userAgent);

        $this->teardown(); //Clear previous result
        $this->curlifier->request();
        //New request should hould the same userAgent
        $this->assertHttpSuccess()
            ->assertUserAgent($userAgent);
    }

    public function testRequestWithUserAgent()
    {
        $userAgent = 'FakeUserAgent 1.2';
        $this->curlifier
            ->request(array(
                'userAgent' => $userAgent
            ));

        $this->assertHttpSuccess()
            ->assertUserAgent($userAgent);

        $this->teardown(); //Clear previous result
        $this->curlifier->request();
        //New request should be made with default userAgent
        $this->assertHttpSuccess()
            ->assertUserAgent('cURL');
    }


    public function testRequestWithCookie()
    {
        $this->curlifier->setCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->request();

        $this
            ->assertHttpSuccess()
            ->assertCookie('FUUU_X_COOKIE', 'QWERTY1234');
    }

    public function testCookieRemainsBetweenRequests()
    {
        $this->curlifier->setCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->request();

        $this
            ->assertHttpSuccess()
            ->assertCookie('FUUU_X_COOKIE', 'QWERTY1234');

        $this->teardown(); //Clear results from previous request
        $this->curlifier->request();
        $this->assertCookie('FUUU_X_COOKIE', 'QWERTY1234');
    }

    public function testMultipleCookiesRemainsBetweenRequests()
    {
        $this->curlifier
            ->addCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->addCookie('TEMPORARY', '1324567890')
            ->request();

        $this
            ->assertHttpSuccess()
            ->assertCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->assertCookie('TEMPORARY', '1324567890');

        $this->teardown(); //Clear results from previous request
        $this->curlifier
            ->request();

        $this->assertCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->assertCookie('TEMPORARY', '1324567890');
    }

    public function testUnsettingCookieSuccees()
    {
        $this->curlifier
            ->addCookie('FUUU_X_COOKIE', 'QWERTY1234')
            ->request();

        $this
            ->assertHttpSuccess()
            ->assertCookie('FUUU_X_COOKIE', 'QWERTY1234');

        $this->teardown(); //Clear results from previous request

        $this->curlifier
            ->removeCookie('FUUU_X_COOKIE') //Remove other cookie
            ->request();

        $this->assertCookieNotSet('FUUU_X_COOKIE');
    }

    /**
     * Assertion functions
     */

    protected function assertHttpSuccess()
    {
        return $this->assertHttpCode(200, 'Did not receive HTTP-code 200');
    }

    protected function assertHttpCode($httpCode, $messageIfNot = null)
    {
        $this->assertSame(
            $httpCode,
            $this->curlifier->getHttpCode(),
            $messageIfNot
        );
        return $this;
    }

    protected function readRequestMirror()
    {
        if ($this->requestRead)
        {
            return $this;
        }
        $requestResult = json_decode($this->curlifier->getBody(), true);
        $this->header = $requestResult['header'];
        $this->get = $requestResult['get'];
        $this->post = $requestResult['post'];
        $this->cookie = $requestResult['cookie'];
        return $this;
    }

    protected function assertGetNotEmpty()
    {
        $this->readRequestMirror();
        $this->assertNotEmpty($this->get, "Get parameters were empty");
        return $this;
    }

    protected function assertGetEmpty()
    {
        $this->readRequestMirror();
        $this->assertEmpty(
            $this->get,
            'Get parameters were not empty: '.var_export($this->get, true)
        );
        return $this;
    }

    protected function assertGetHasKeys($listOfKeys)
    {
        $this->readRequestMirror();
        foreach ($listOfKeys as $expectedKey)
        {
            $this->assertArrayHasKey($expectedKey, $this->get);
        }
        return $this;
    }

    protected function assertGetHasValues($keyValuePairs)
    {
        $this->readRequestMirror();
        foreach ($keyValuePairs as $expectedKey => $expectedValue)
        {
            $this->assertArrayHasKey($expectedKey, $this->get);
            $this->assertSame((string)$expectedValue, $this->get[$expectedKey]);
        }
        return $this;
    }

    protected function assertPostNotEmpty()
    {
        $this->readRequestMirror();
        $this->assertNotEmpty($this->post, "POST parameters were empty, expected not to");
        return $this;
    }

    protected function assertPostEmpty()
    {
        $this->readRequestMirror();
        $this->assertEmpty(
            $this->post,
            'POST parameters were not empty: '.var_export($this->post, true)
        );
        return $this;
    }

    protected function assertPostHasKeys($listOfKeys)
    {
        $this->readRequestMirror();
        foreach ($listOfKeys as $expectedKey)
        {
            $this->assertArrayHasKey($expectedKey, $this->post);
        }
        return $this;
    }

    protected function assertPostHasValues($keyValuePairs)
    {
        $this->readRequestMirror();
        foreach ($keyValuePairs as $expectedKey => $expectedValue)
        {
            $this->assertArrayHasKey($expectedKey, $this->post);
            $this->assertSame((string)$expectedValue, $this->post[$expectedKey]);
        }
        return $this;
    }

    protected function assertHeaderReferer($expected)
    {
        $this->assertHeaderValue('HTTP_REFERER', $expected);
        return $this;
    }

    protected function assertHeaderHasKeys($listOfKeys)
    {
        foreach ($listOfKeys as $expectedKey)
        {
            $this->assertArrayHasKey(
                $expectedKey,
                $this->header,
                "Header did not have expected key '$expectedKey'"
            );
        }
        return $this;
    }

    protected function assertHeaderValue($key, $value)
    {
        $this->readRequestMirror();
        $this->assertArrayHasKey($key, $this->header);
        $this->assertSame($value, $this->header[$key]);
        return $this;
    }

    protected function assertUserAgent($userAgent)
    {
        $this->readRequestMirror();
        $this->assertArrayHasKey(
            'HTTP_USER_AGENT',
            $this->header
        );
        $this->assertSame($userAgent, $this->header['HTTP_USER_AGENT']);
        return $this;
    }

    protected function assertCookieNotSet($nameOfCookie)
    {
        //If no cookies send -> given cookie cannot be set
        if ( !empty($this->cookie))
        {
            $this->assertArrayNotHasKey($nameOfCookie, $this->cookie);
        }
        return $this;
    }

    protected function assertCookieEmpty()
    {
      $this->readRequestMirror();
      $this->assertEmpty($this->cookie);
      return $this;
    }

    protected function assertCookie($key, $value)
    {
        $this->readRequestMirror();
        $this->assertArrayHasKey($key, $this->cookie);
        $this->assertSame($value, $this->cookie[$key]);
        return $this;
    }

    protected function dumpHeaders()
    {
        $this->readRequestMirror();
        D($this->header);
        return $this;
    }
}