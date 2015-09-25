<?php

namespace Psecio\PropAuth;

class Enforcer
{
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
