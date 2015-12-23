<?php
namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Check;
use Psecio\PropAuth\Policy;

class TestPasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * When a single password string is provided, evaluate for a match
     */
    public function testEqualsValidMatchSingle()
    {
        $data = password_hash('testing1234', PASSWORD_DEFAULT);

        $check = new Check('equals', 'testing1234');
        $test = new TestPassword($check);

        $this->assertTrue($test->evaluate($data));
    }

    /**
     * When multiple values are given, search until a match is found
     */
    public function testEqualsValidMatchMultiple()
    {
        $data = password_hash('testing1234', PASSWORD_DEFAULT);

        $check = new Check('equals', ['foo', 'testing1234']);
        $test = new TestPassword($check);

        $this->assertTrue($test->evaluate($data));
    }

    /**
     * When multiple values are given, search until a match is found
     */
    public function testEqualsInvalidMatchMultiple()
    {
        $data = password_hash('testing1234', PASSWORD_DEFAULT);

        $check = new Check('equals', ['foo', 'bar']);
        $test = new TestPassword($check);

        $this->assertFalse($test->evaluate($data));
    }

    /**
     * When a null value is given it will return false
     */
    public function testEqualsInvalidMatcNull()
    {
        $data = password_hash('testing1234', PASSWORD_DEFAULT);

        $check = new Check('equals', null);
        $test = new TestPassword($check);

        $this->assertFalse($test->evaluate($data));
    }

    /**
     * When not equals is called, it will always return false
     */
    public function testNotEqualsValidMatch()
    {
        $data = password_hash('testing1234', PASSWORD_DEFAULT);

        $check = new Check('not-equals', 'foo');
        $test = new TestPassword($check);

        $this->assertFalse($test->evaluate($data));
    }
}
