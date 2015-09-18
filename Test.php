<?php

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

    abstract public function evaluate($value);
}