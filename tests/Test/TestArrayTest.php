<?php
namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Check;
use Psecio\PropAuth\Policy;

class TestTestArray extends \PHPUnit_Framework_TestCase
{
    public function checkProvider()
    {
        return [
            // --- EQUALS -----
            // ANY match on string from array
            ['equals', 'foo', ['foo', 'bar'], Policy::ANY, true],
             // No match with ANY in array values
            ['equals', 'nomatch', ['foo', 'bar'], Policy::ANY, false],
             // ANY match where at least one value is in array
            ['equals', ['foo'], ['foo', 'bar'], Policy::ANY, true],
             // No match with ANY, no value exists in array
            ['equals', ['nomatch'], ['foo', 'bar'], Policy::ANY, false],

            // --- NOT EQUALS -----
            // ANY match, value not found, string
            ['not-equals', 'baz', ['foo', 'bar'], Policy::ANY, true],
            // ANY match, value is found, string
            ['not-equals', 'foo', ['foo', 'bar'], Policy::ANY, false],
            // ANY match, value not found, string
            ['not-equals', ['baz'], ['foo', 'bar'], Policy::ANY, true],
            // ANY match, value is found, string
            ['not-equals', ['foo'], ['foo', 'bar'], Policy::ANY, false],
        ];
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCheckTypes($type, $match, $data, $policy, $result)
    {
        $check = new Check($type, $match, ['rule' => $policy]);
        $test = new TestArray($check);

        if ($result == true) {
            $this->assertTrue($test->evaluate($data));
        } else {
            $this->assertFalse($test->evaluate($data));
        }
    }

    /**
     * Expect an exception when the Check type is invalid on evaluation
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidType()
    {
        $check = new Check('array', null);
        $test = new TestArray($check);

        $test->evaluate(null, null);
    }
}
