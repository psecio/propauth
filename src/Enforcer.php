<?php

namespace Psecio\PropAuth;

class Enforcer
{
    public function evaluate(User $subject, Policy $policy)
    {
        $pass = true;

        foreach ($policy->getChecks() as $type => $value) {
            $propertyValue = $subject->getProperty($type);
            $valueType = gettype($propertyValue);

            // Ensure all of the things in our policy are true
            foreach ($value as $test) {
                $typeNs = __NAMESPACE__.'\Test\Test'.ucwords(strtolower($valueType));
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
