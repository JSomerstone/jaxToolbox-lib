<?php
namespace test;

class CustomAssert extends \PHPUnit_Framework_TestCase
{

    protected function assertArrayHasExactKeys($expectedKeys, $actualArray)
    {
        $actualKeys = array_keys($actualArray);

        $this->assertSame($expectedKeys, $actualKeys);
        return $this;
    }

    protected function assertArrayHasKeys($expectedKeys, $actualArray)
    {
        foreach ($expectedKeys as $aKey)
        {
            $this->assertArrayHasKey($aKey, $actualArray);
        }
        return $this;
    }
}