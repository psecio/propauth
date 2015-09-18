<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;

class TestPassword extends \Psecio\PropAuth\Test
{
    /**
     * Evaluate for a password hash match (uses bcrypt password hashing)
     *
     * @param string $value Plain-text password input
     * @param string $compare Hash to compare to
     * @return boolean Pass/fail result of evaluation
     */
    protected function evaluateEquals($value, $compare)
    {
        if (is_array($value)) {
            foreach ($value as $password) {
                if (password_verify($password, $compare) === true) {
                    return true;
                }
            }
            return false;
        } elseif (is_string($value)) {
            return password_verify($value, $compare);
        }

        return false;
    }

    protected function evaluateNotEquals($value, $compare)
    {
        // Can't imagine why you'd want to do this....
        return false;
    }
}