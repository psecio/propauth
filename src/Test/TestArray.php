<?php

namespace Psecio\PropAuth\Test;

class TestArray extends \Psecio\PropAuth\Test
{
    protected function evaluateEquals($value, $compare)
    {
        $test = $this->getTest();

        if (is_string($value)) {
            if ($test['addl']['rule'] === Policy::ANY) {
                return in_array($value, $compare);
            } elseif ($test['addl']['rule'] === Policy::ALL) {
                // ensure all items match
                foreach ($compare as $v) {
                    if ($v !== $value) {
                        return false;
                    }
                }
            }
        } elseif (is_array($value)) {
            if ($test['addl']['rule'] === Policy::ANY) {
                // see if any of the values match
                foreach ($compare as $v) {
                    if ($v == $value) {
                        return true;
                    }
                }
            } elseif ($test['addl']['rule'] === Policy::ALL) {
                // see if all values match
                return $compare == $value;
            }
        }
    }

    protected function evaluateNotEquals($value, $compare)
    {
        if (is_string($value)) {
            if ($test['addl']['rule'] === Policy::ANY) {
                return !in_array($value, $compare);
            } elseif ($test['addl']['rule'] === Policy::ALL) {
                $fail = false;
                foreach ($compare as $v) {
                    if ($v == $value && $fail === false) {
                        $fail = true;
                    }
                }
                // If we failed, return false
                return ($fail === true) ? false : true;
            }

            return (!in_array($value, $compare));

        } elseif (is_array($value)) {
            if ($test['addl']['rule'] === Policy::ANY) {

                // see if any of our values are in the array
                $fail = false;
                foreach ($value as $v) {
                    if (in_array($v, $compare) && $fail == false) {
                        $fail = true;
                    }
                }
                // If we failed, return false
                return ($fail === true) ? false : true;

            } elseif ($test['addl']['rule'] === Policy::ALL) {
                return $compare !== $value;
            }
        } else {
            return $test['value'] !== $compare;
        }
    }
}