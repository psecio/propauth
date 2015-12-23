<?php

namespace Psecio\PropAuth;

abstract class Test
{
    protected $test;
    protected $addl = array();

    public function __construct($test, array $addl = array())
    {
        $this->setTest($test);
        $this->setAdditional($addl);
    }

    public function setAdditional(array $addl)
    {
        $this->addl = $addl;
    }
    public function getAdditional()
    {
        return $this->addl;
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
        throw new \InvalidArgumentException('Type "'.$test->getType().'" is invalid!');
    }

    abstract protected function evaluateEquals($value, $compare);
    abstract protected function evaluateNotEquals($value, $compare);
}
