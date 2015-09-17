<?php

// property-based auth*

class Enforcer
{
    public function evaluate(User $subject, Policy $policy)
    {
        echo '-------- EVAL'."\n";

        $pass = true;

        foreach ($policy->getChecks() as $type => $value) {
            echo $type.' -> '.print_r($value, true)."\n";

            $propertyValue = $subject->getProperty($type);
            echo 'property value: '.print_r($propertyValue, true)."\n";
            $valueType = gettype($propertyValue);

            // Ensure all of the things in our policy are true
            foreach ($value as $test) {
                echo 'test: '.print_r($test, true)."\n";

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
        $value = $test[1];

print_r($test);

        if ($test[0] == 'equals') {
            return (is_string($value)) ? (in_array($value, $compare)) : $test[1] == $compare;
        } elseif ($test[0] == 'not-equals') {
            if (is_string($value)) {
                return (!in_array($value, $compare));
            } elseif (is_array($value)) {
                echo 'ARRAY';
            } else {
                return $test[1] !== $compare;
            }
        }
    }
    private function testString($test, $compare)
    {
        $value = $test[1];

        if ($test[0] == 'equals' && $compare !== $test[1]) {
            return false;
        } elseif ($test[0] == 'not-equals' && $compare == $test[1]) {
            return false;
        }

        return true;
    }
}

class Policy
{
    private $checks = [];

    public function has($type, $value)
    {
        if (!isset($this->checks[$type])) {
            $this->checks[$type] = [];
        }
        if (is_string($value)) {
            $matchType = 'equals';
        } elseif (is_array($value)) {
            $matchType = 'contains';
        }
        $this->checks[$type][] = [$matchType, $value];
        return $this;
    }

    public function __call($name, $args)
    {
        echo $name.' -> '.print_r($args, true)."\n";

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

        if (is_string($value)) {
            $matchType = 'equals';
        } elseif (is_array($value)) {
            $matchType = 'contains';
        }
        $this->checks[$type][] = [$matchType, $value];
    }
    private function notCheck($type, $name, $args)
    {
        $value = array_shift($args);

        // if (is_string($value)) {
        //     $matchType = 'not-equals';
        // } elseif (is_array($value)) {
        //     $matchType = 'not-contains';
        // }
        $matchType = 'not-equals';
        $this->checks[$type][] = [$matchType, $value];
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
    'username' => 'ccornutt',
    'permissions' => ['test1']
]);

$myPolicy = new Policy();
$myPolicy->hasUsername('ccornutt')->notPermissions(['test']);

// print_r($myPolicy);

$result = $enforcer->evaluate($myUser, $myPolicy);

echo 'RESULT: '.var_export($result, true)."\n\n";