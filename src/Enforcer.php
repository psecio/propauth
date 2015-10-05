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
            $this->policySet = $policySet;
        }
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
     * @return boolean Pass/fail status of evaluation
     */
    public function evaluate($subject, Policy $policy, array $addl = array())
    {
        $pass = true;
        $addl = array_merge([$subject], $addl);

        foreach ($policy->getChecks() as $type => $value) {
            $method = 'get'.ucwords(strtolower($type));
            $propertyValue = null;

            if ($type !== 'closure' && (isset($subject->$type) || $subject->$type !== null)) {
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
                    $testInstance = new $typeNs($test, $addl);
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
