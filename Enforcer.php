<?php

class Enforcer
{
    public function evaluate(User $subject, Policy $policy)
    {
        $pass = true;

        foreach ($policy->getChecks() as $type => $value) {
            // echo $type.' -> '.print_r($value, true)."\n";

            $propertyValue = $subject->getProperty($type);
            // echo 'property value: '.print_r($propertyValue, true)."\n";
            $valueType = gettype($propertyValue);

            // Ensure all of the things in our policy are true
            foreach ($value as $test) {
                // echo 'test: '.print_r($test, true)."\n";

                if ($valueType == 'string' && $this->testString($test, $propertyValue) === false) {
                    return false;
                }
                if ($valueType == 'array' && $this->testArray($test, $propertyValue) === false) {
                    return false;
                }
            }
        }
        return $pass;
    }

    private function testArray($test, $compare)
    {
        $value = $test['value'];

        if ($test['type'] == 'equals') {
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

        } elseif ($test['type'] == 'not-equals') {
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
    private function testString($test, $compare)
    {
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
