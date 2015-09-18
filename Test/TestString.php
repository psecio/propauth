<?php

class TestString extends Test
{
    public function evaluate($compare)
    {
        $test = $this->getTest();
        $value = $test->getValue();

        if ($test->getType() == 'equals') {
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
        } elseif ($test->getType() == 'not-equals') {
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

        if ($test->getType() == 'equals' && $compare !== $test->getValue()) {
            return false;
        } elseif ($test->getType() == 'not-equals' && $compare == $test->getValue()) {
            return false;
        }

        return true;
    }
}