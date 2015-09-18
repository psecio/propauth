<?php

namespace Psecio\PropAuth;

abstract class Test
{
    protected $test;

    public function __construct($test)
    {
        $this->setTest($test);
    }

    public function setTest($test)
    {
        $this->test = $test;
    }
    public function getTest()
    {
        return $this->test;
    }

    public function evaluate($compare)
    {
        $test = $this->getTest();
        $value = $test->getValue();

        switch($test->getType()) {
            case 'equals':
                return $this->evaluateEquals($value, $compare);
            case 'not-equals':
                return $this->evaluateNotEquals($value, $compare);
        }

        return true;
    }

    abstract protected function evaluateEquals($value, $compare);
    abstract protected function evaluateNotEquals($value, $compare);
}