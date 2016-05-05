<?php

namespace Psecio\PropAuth;

class Enforcer
{
    /**
     * Set of policies for evluation
     * @var array
     */
    private $policySet = [];

    /**
     * Static method to get a new instance of the Enforcer object
     *
     * @param \Psecio\PropAuth\PolicySet|null $policySet Set of policies [optional]
     * @return \Psecio\PropAuth\Enforcer instance
     */
    public static function instance(\Psecio\PropAuth\PolicySet $policySet = null)
    {
        return new Enforcer($policySet);
    }

    /**
     * Init the object, possibly with an optional policy set
     *
     * @param \Psecio\PropAuth\PolicySet|null $policySet Set of Policy instances [optional]
     */
    public function __construct(\Psecio\PropAuth\PolicySet $policySet = null)
    {
        if ($policySet !== null) {
            $this->setPolicySet($policySet);
        }
    }

    /**
     * Set the current policy set (overrides, not appends)
     *
     * @param \Psecio\PropAuth\PolicySet $policySet Set of Policy objects
     * @return \Psecio\PropAuth\Enforcer instance
     */
    public function setPolicySet(\Psecio\PropAuth\PolicySet $policySet)
    {
        $this->policySet = $policySet;
        return $this;
    }

    /**
     * Locate a policy by key name and determine if the subject is allowed
     *     by matching against its properties
     *
     * @param string $policyName Policy name to evaluate
     * @param object $subject Subject to evaluate against
     * @throws \InvalidArgumentException If policy name is not found
     * @return boolean Pass/fail result of evaluation
     */
    public function allows($policyName, $subject, array $addl = array())
    {
        if (!is_array($policyName)) {
            $policyName = [$policyName];
        }
        foreach ($policyName as $name) {
            if (!isset($this->policySet[$name])) {
                throw new \InvalidArgumentException('Policy name "'.$name.'" not found');
            }
            $result = $this->evaluate($subject, $this->policySet[$name], $addl);
            if ($result === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Locate a policy by name and evaluate if the subject is denied
     *     by matching against its properties
     *
     * @param string $policyName Policy key name
     * @param object $subject Subject to evluate against
     * @throws \InvalidArgumentException If policy name is not found
     * @return boolean Pass/fail of evluation
     */
    public function denies($policyName, $subject, array $addl = array())
    {
        if (!is_array($policyName)) {
            $policyName = [$policyName];
        }
        foreach ($policyName as $name) {
            if (!isset($this->policySet[$name])) {
                throw new \InvalidArgumentException('Policy name "'.$name.'" not found');
            }
            $result = $this->evaluate($subject, $this->policySet[$name], $addl);
            if ($result === true) {
                return false;
            }
        }
        return true;
    }

    /**
     * Given a subject and a policy, evaluate the pass/fail result of the matching
     *
     * @param object $subject Subject to match against
     * @param \Psecio\PropAuth\Policy $policy Policy to evaluate
     * @throws \InvalidArgumentException If the property value is invalid (null)
     * @return boolean Pass/fail status of evaluation
     */
    public function evaluate($subject, Policy $policy, array $addl = array())
    {
        $addl = array_merge([$subject], $addl);

        $checks = $policy->getChecks();
        if (empty($checks)) {
            trigger_error('Policy evaluation was perfomed with no checks', E_USER_WARNING);
        }

        foreach ($checks as $type => $value) {
            $propertyValue = $this->getPropertyValue($type, $subject);

            if ($propertyValue == null && $type !== 'closure') {
                throw new \InvalidArgumentException('Invalid property value for "'.$type.'"!');
            }

            $result = $this->executeTests($value, $type, $propertyValue, $addl);

            // If we have a failure, return false...
            if ($result === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute the tests on the policy to ensure they match/pass
     *
     * @param array $tests A set of Check instances to evaluate
     * @param string $type Type of tests to evaluate with
     * @param string $propertyValue Value to evaluate against
     * @param array $addl Additional values to pass through to the test
     * @throws \InvalidArgumentException If the type of test (class) does not exist
     * @return boolean Pass/fail status of the evaluation (if exception not thrown)
     */
    public function executeTests($tests, $type, $propertyValue, $addl)
    {
        $valueType = gettype($propertyValue);

        // Ensure all of the things in our policy are true
        foreach ($tests as $test) {
            // First check for a custom "tester"
            $typeNs = __NAMESPACE__.'\Test\Test'.ucwords(strtolower($type));
            if (!class_exists($typeNs)) {
                $typeNs = __NAMESPACE__.'\Test\Test'.ucwords(strtolower($valueType));
            }

            if (class_exists($typeNs)) {
                $testInstance = new $typeNs($test, $addl);
                if ($testInstance->evaluate($propertyValue) === false) {
                    return false;
                }
            } else {
                throw new \InvalidArgumentException('Test type "'.$valueType.'" does not exist.');
            }
        }
        return true;
    }

    /**
     * Type a few options to get the property value for evaluation
     *
     * @param string $type Type of check being performed
     * @param object $subject Object to get the property value from
     * @return mixed Either the found property value or null if not found
     */
    public function getPropertyValue($type, $subject)
    {
        $method = 'get'.ucwords(strtolower($type));
        $propertyValue = null;

        if (($type !== 'closure' && $type !== 'method') && (isset($subject->$type) && $subject->$type !== null)) {
            $propertyValue = $subject->$type;
        } elseif (method_exists($subject, $method)) {
            $propertyValue = $subject->$method();
        } elseif (method_exists($subject, 'getProperty')) {
            $propertyValue = $subject->getProperty($type);
        }

        return $propertyValue;
    }
}
