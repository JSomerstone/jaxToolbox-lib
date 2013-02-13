<?php
namespace test\acceptance\lib;

class ConsoleRequestTest extends \test\acceptance\AcceptanceTestCase
{
    /**
     * @var \jaxToolbox\lib\ConsoleRequest
     */
    protected $consoleRequest;

    public function setUp()
    {
        $this->consoleRequest = new \jaxToolbox\lib\ConsoleRequest();
    }

    public function singleParameterProvider()
    {
       return array(
           array(
               array('-a', '2'),
               array('a' => '2')
           ),
           array(
               array('-a'),
               array('a' => true)
           ),
           array(
               array('-ab'),
               array('a' => true, 'b' => true)
           ),
           array(
               array('-ab', 'three'),
               array('a' => true, 'b' => 'three')
           ),

           array(
               array('-a', 'fox jumped over lazy dog'),
               array('a' => 'fox jumped over lazy dog')
           ),
       );
    }

    /**
     * @dataProvider singleParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::getArguments()
     *
     * @param type $input
     * @param type $expected
     */
    public function testSingleCharRequests($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        $this->assertSame(
            $expected,
            $this->consoleRequest->getArguments()
        );
    }

    public function multipleCharParameterProvider()
    {
        return array(
            array(
                array('--foobar'),
                array('foobar' => true)
            ),
            array(
                array('--foobar', 'bogus'),
                array('foobar' => 'bogus')
            ),
            array(
                array('--foobar', '1',  '--bogus'),
                array('foobar' => '1', 'bogus' => true)
            ),
        );
    }

    /**
     * @dataProvider multipleCharParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::getArguments()
     *
     * @param type $input
     * @param type $expected
     */
    public function testMultipleCharRequests($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        $this->assertSame(
            $expected,
            $this->consoleRequest->getArguments()
        );
    }


    public function mixedParameterProvider()
    {
        return array(
            array(
                array('-a', '--abba'),
                array('a' => true, 'abba' => true)
            ),
            array(
                array('-a', 'X', '--abba', 'Y'),
                array('a' => 'X', 'abba' => 'Y')
            ),
            array(
                array('-ab', 'X', '--abba', 'Y'),
                array('a' => true, 'b' => 'X', 'abba' => 'Y')
            ),
        );
    }

    /**
     * @dataProvider mixedParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::getArguments()
     *
     * @param type $input
     * @param type $expected
     */
    public function testMixedParameterRequests($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        $this->assertSame(
            $expected,
            $this->consoleRequest->getArguments()
        );
    }

    /**
     * @dataProvider mixedParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::get()
     *
     * @param type $input
     * @param type $expected
     */
    public function testGetterWithMixed($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        foreach ($expected as $key => $expectedValue)
        {
            $this->assertSame(
                $expectedValue,
                $this->consoleRequest->get($key)
            );
        }
    }

    /**
     * @dataProvider multipleCharParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::get()
     *
     * @param type $input
     * @param type $expected
     */
    public function testGetterWithMultiChar($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        foreach ($expected as $key => $expectedValue)
        {
            $this->assertSame(
                $expectedValue,
                $this->consoleRequest->get($key)
            );
        }
    }

    /**
     * @dataProvider singleParameterProvider
     * @covers \jaxToolbox\lib\ConsoleRequest::get()
     *
     * @param type $input
     * @param type $expected
     */
    public function testGetterWithSingleChar($input, $expected)
    {
        $this->consoleRequest->setInput($input);
        foreach ($expected as $key => $expectedValue)
        {
            $this->assertSame(
                $expectedValue,
                $this->consoleRequest->get($key)
            );
        }
    }

    /**
     * @covers  \jaxToolbox\lib\ConsoleRequest::hasArguments()
     * @covers  \jaxToolbox\lib\ConsoleRequest::hasArgument()
     */
    public function testHasArgument()
    {
        $this->consoleRequest->setInput(array(
            '-a', '1', '-b', '2', '--help'
        ));

        $this->assertTrue($this->consoleRequest->hasArgument('a'));
        $this->assertTrue($this->consoleRequest->hasArgument('b'));
        $this->assertTrue($this->consoleRequest->hasArgument('help'));

        $this->assertTrue($this->consoleRequest->hasArguments('a', 'b', 'help'));

        $this->assertFalse($this->consoleRequest->hasArgument('x'));
        $this->assertFalse($this->consoleRequest->hasArgument('c'));
        $this->assertFalse($this->consoleRequest->hasArgument('pleh'));

        $this->assertFalse($this->consoleRequest->hasArguments('a', 'b', 'pleh'));

    }
}