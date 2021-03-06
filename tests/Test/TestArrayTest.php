<?php
namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Check;
use Psecio\PropAuth\Policy;

class TestArrayTest extends \PHPUnit_Framework_TestCase
{
    public function checkProvider()
    {
        return [
            // --- EQUALS, ANY -----
            'equals-string-any-match' => ['equals', 'foo', ['foo', 'bar'], Policy::ANY, true],
            'equals-string-any-nomatch' => ['equals', 'nomatch', ['foo', 'bar'], Policy::ANY, false],
            'equals-array-any-match' => ['equals', ['foo'], ['foo', 'bar'], Policy::ANY, true],
            'equals-array-any-nomatch' => ['equals', ['nomatch'], ['foo', 'bar'], Policy::ANY, false],

            // --- NOT EQUALS, ANY -----
            'notequals-string-any-match' => ['not-equals', 'baz', ['foo', 'bar'], Policy::ANY, true],
            'notequals-string-any-nomatch' => ['not-equals', 'foo', ['foo', 'bar'], Policy::ANY, false],
            'notequals-array-any-match' => ['not-equals', ['baz'], ['foo', 'bar'], Policy::ANY, true],
            'notequals-array-any-nomatch' => ['not-equals', ['foo'], ['foo', 'bar'], Policy::ANY, false],

            // --- EQUALS, ALL -----
            'equals-string-all-match' => ['equals', 'foo', ['foo', 'foo'], Policy::ALL, true],
            'equals-string-all-nomatch' => ['equals', 'bar', ['foo', 'foo'], Policy::ALL, false],
            'equals-array-all-match' => ['equals', ['foo', 'foo'], ['foo', 'foo'], Policy::ALL, true],
            'equals-array-all-nomatch' => ['equals', ['bar'], ['foo', 'foo'], Policy::ALL, false],

            // --- NOT EQUALS, ALL -----
            'notequals-string-all-match' => ['not-equals', 'foo', ['foo', 'foo'], Policy::ALL, false],
            'notequals-string-all-nomatch' => ['not-equals', 'bar', ['foo', 'foo'], Policy::ALL, true],
            'notequals-array-all-match' => ['not-equals', ['foo', 'foo'], ['foo', 'foo'], Policy::ALL, false],
            'notequals-array-all-nomatch' => ['not-equals', ['bar'], ['foo', 'foo'], Policy::ALL, true],

            // Oddities...
            'notequals-string-all-nomatch-nopolicy' => ['not-equals', 'bar', ['foo', 'foo'], null, true],
            'notequals-array-all-nomatch-nopolicy' => ['not-equals', new \stdClass(), ['foo', 'foo'], null, true],
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
