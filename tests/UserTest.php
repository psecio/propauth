<?php

namespace Psecio\PropAuth;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getter/setter methods for properties
     */
    public function testGetSetProperty()
    {
        $user = new User([]);
        $key = 'foo';
        $value = 'testing123';

        $user->addProperty($key, $value);
        $this->assertEquals($value, $user->getProperty($key));
    }
}
