<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;

class TestString extends \Psecio\PropAuth\Test
{
    protected function evaluateEquals($value, $compare)
    {
        $test = $this->getTest();

        if (is_array($value)) {
            if ($test->getAddl()['rule'] === Policy::ANY) {
                return (in_array($compare, $value));
            } elseif ($test->getAddl()['rule'] === Policy::ALL) {
                return $compare == $value;
            }

        } elseif (is_string($value)) {
            // Comparing a string to a string
            return $compare == $value;
        }
    }

    protected function evaluateNotEquals($value, $compare)
    {
        $test = $this->getTest();

        if (is_array($value)) {
            if ($test->getAddl()['rule'] === Policy::ANY) {
                return (!in_array($compare, $value));
            } elseif ($test->getAddl()['rule'] === Policy::ALL) {
                return $compare !== $value;
            }

        } elseif (is_string($value)) {
            // Comparing a string to a string
            return $compare !== $value;
        }
    }
}