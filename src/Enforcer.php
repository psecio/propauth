<?php

namespace Psecio\PropAuth;

class Enforcer
{
    private $policySet = [];

    public function __construct(\Psecio\PropAuth\PolicySet $policySet = null)
    {
        if ($policySet !== null) {
            $this->policySet = $policySet;
        }
    }

    public function allows($policyName, $subject)
    {
        if (!is_array($policyName)) {
            $policyName = [$policyName];
        }
        foreach ($policyName as $name) {
            if (!isset($this->policySet[$name])) {
                throw new \InvalidArgumentException('Policy name "'.$name.'" not found');
            }
            $result = $this->evaluate($subject, $this->policySet[$name]);
            if ($result === false) {
                return false;
            }
        }
        return true;
    }
    public function denies($policyName, $subject)
    {
        if (!is_array($policyName)) {
            $policyName = [$policyName];
        }
        foreach ($policyName as $name) {
            if (!isset($this->policySet[$name])) {
                throw new \InvalidArgumentException('Policy name "'.$name.'" not found');
            }
            $result = $this->evaluate($subject, $this->policySet[$name]);
            if ($result === true) {
                return false;
            }
        }
        return true;
    }

    public function evaluate($subject, Policy $policy)
    {
        $pass = true;

        foreach ($policy->getChecks() as $type => $value) {
            $method = 'get'.ucwords(strtolower($type));
            $propertyValue = null;

            if (isset($subject->$type)) {
                $propertyValue = $subject->$type;
            } elseif (method_exists($subject, $method)) {
                $propertyValue = $subject->$method();
            } elseif (method_exists($subject, 'getProperty')) {
                $propertyValue = $subject->getProperty($type);
            }
            $valueType = gettype($propertyValue);

            // Ensure all of the things in our policy are true
            foreach ($value as $test) {
                // First check for a custom "tester"
                $typeNs = __NAMESPACE__.'\Test\Test'.ucwords(strtolower($type));
                if (!class_exists($typeNs)) {
                    $typeNs = __NAMESPACE__.'\Test\Test'.ucwords(strtolower($valueType));
                }

                if (class_exists($typeNs)) {
                    $testInstance = new $typeNs($test);
                    if ($testInstance->evaluate($propertyValue) === false) {
                        return false;
                    }
                } else {
                    throw new \InvalidArgumentException('Test type "'.$valueType.'" does not exist.');
                }
            }
        }
        return $pass;
    }
}
