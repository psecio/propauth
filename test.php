<?php

// property-based auth*

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
        $value = $test['value'];

        if ($test['type'] == 'equals') {
            if (is_array($value)) {
                if ($test['addl']['rule'] === Policy::ANY) {
                    return (in_array($compare, $value));
                } elseif ($test['addl']['rule'] === Policy::ALL) {
                    return $compare == $value;
                }

            } elseif (is_string($value)) {
                // Comparing a string to a string
                return $compare == $value;
            }
        } elseif ($test['type'] == 'not-equals') {
            if (is_array($value)) {
                if ($test['addl']['rule'] === Policy::ANY) {
                    return (!in_array($compare, $value));
                } elseif ($test['addl']['rule'] === Policy::ALL) {
                    return $compare !== $value;
                }

            } elseif (is_string($value)) {
                // Comparing a string to a string
                return $compare !== $value;
            }
        }

        if ($test['type'] == 'equals' && $compare !== $test['value']) {
            return false;
        } elseif ($test['type'] == 'not-equals' && $compare == $test['value']) {
            return false;
        }

        return true;
    }
}

class Policy
{
    private $checks = [];

    const ANY = 'any';
    const ALL = 'all';
    const NONE = 'none';

    public function __call($name, $args)
    {
        echo 'CALL: '.$name.' -> '.print_r($args, true)."\n";

        // look for a "keyword"
        if (strpos($name, 'has') === 0) {
            $type = strtolower(str_replace('has', '', $name));
            $this->hasCheck($type, $name, $args);
        } elseif (strpos($name, 'not') === 0) {
            $type = strtolower(str_replace('not', '', $name));
            $this->notCheck($type, $name, $args);
        }

        return $this;
    }

    private function hasCheck($type, $name, $args)
    {
        $value = array_shift($args);
        $matchType = 'equals';

        if (!isset($args[0])) {
            $args[0] = ['rule' => Policy::ANY];
        }

        // see what other options we've been given
        if (is_string($args[0]) && ($args[0] === Policy::ANY || $args[0] === Policy::ALL) ) {
            $args['rule'] = $args[0];
            unset($args[0]);
        } elseif (is_array($args[0])) {
            $args = $args[0];
        }

        $this->checks[$type][] = ['type' => $matchType, 'value' => $value, 'addl' => $args];
    }
    private function notCheck($type, $name, $args)
    {
        $value = array_shift($args);
        $matchType = 'not-equals';


        if (!isset($args[0])) {
            $args[0] = ['rule' => Policy::ANY];
        }

        // see what other options we've been given
        if (is_string($args[0]) && ($args[0] === Policy::ANY || $args[0] === Policy::ALL) ) {
            $args['rule'] = $args[0];
            unset($args[0]);
        } elseif (is_array($args[0])) {
            $args = $args[0];
        }

        $this->checks[$type][] = ['type' => $matchType, 'value' => $value, 'addl' => $args];
    }

    public function getChecks()
    {
        return $this->checks;
    }
}

class User
{
    protected $properties = [];

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->addProperty($name, $value);
        }
    }

    public function addProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function getProperty($name)
    {
        return (isset($this->properties[$name])) ? $this->properties[$name] : null;
    }
}


// -------------------------
$enforcer = new Enforcer();
$myUser = new User([
    'username' => ['ccornutt', 'ccornutt1'],
    'permissions' => ['test1']
]);

$myPolicy = new Policy();
// $myPolicy->hasUsername('ccornutt')->notPermissions(['test']);
$myPolicy
    // ->hasUsername(['ccornutt', 'ccornutt1'], Policy::ANY);
    // ->notUsername(['ccornutt', 'ccornutt2'], Policy::ANY);
    ->notUsername(['ccornutt2'], Policy::ANY);
    // ->notPermissions(['test']);

print_r($myPolicy);

$result = $enforcer->evaluate($myUser, $myPolicy);
echo 'RESULT: '.var_export($result, true)."\n\n";

