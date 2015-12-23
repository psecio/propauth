<?php
namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Check;
use Psecio\PropAuth\Policy;

class TestClosureTest extends \PHPUnit_Framework_TestCase
{
    public function testEqualsValidMatch()
    {
        $data = 'testing1234';
        $check = new Check(
            'equals',
            function($value) use ($data) { return ($data == $value); }
        );
        $test = new TestClosure($check, [$data]);

        $this->assertTrue($test->evaluate($data));
    }

    /**
     * Test that true is returned when a "not equals" call
     * 	is made and the closure evaluates to true. In this case, it's
     * 	up to the closure to return a false which the test interprets as success.
     */
    public function testNotEqualsValid()
    {
        $data = 'testing1234';
        $check = new Check(
            'not-equals',
            function($value) { return ('foobar' == $value); }
        );
        $test = new TestClosure($check, [$data]);

        $this->assertTrue($test->evaluate($data));
    }

    /**
     * Expect an exception when not enough paramters are provided
     *
     * @expectedException \Psecio\PropAuth\Exception\MissingParametersException
     */
    public function testEqualsNotEnoughParams()
    {
        $data = 'testing1234';
        $check = new Check(
            'equals',
            function($value) use ($data) { return ($data == $value); }
        );
        $test = new TestClosure($check, []);

        $this->assertTrue($test->evaluate($data));
    }
}
