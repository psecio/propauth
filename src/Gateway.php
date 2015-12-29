<?php

namespace Psecio\PropAuth;
use Psecio\PropAuth\Subject;
use Psecio\PropAuth\Authenticator;
use Psecio\PropAuth\PolicySet;

class Gateway
{
    protected $subject;
    protected $policies;

    public function __construct($subject, Context $context = null)
    {
        $this->subject = ($subject instanceof Subject) ? $subject : new Subject($subject);
        $this->policies = new PolicySet();

        if ($context !== null && $context->get('policies') !== null) {
            $this->setupPolicies($context->get('policies'));
        }
    }

    public function setupPolicies($policies)
    {
        if ($policies instanceof PolicySet) {
            $this->policies = $policies;
        } else {
            foreach ($policies as $name => $policy) {
                $this->policies->add($name, $policy);
            }
        }
    }

    public function authenticate($password)
    {
        $auth = Authenticator::make(
            'password',
            ['password' => $password, 'subject' => $this->subject]
        );
        $result = $auth->evaluate();
        if ($result === true) {
            $this->subject->setAuth(true);
            $this->subject->setPolicies($this->policies);
            return $this->subject;
        }
        return false;
    }

    public function evaluate($policyName = null, $type = Policy::ANY)
    {
        if ($this->subject->isAuthed() === false) {
            throw new \InvalidArgumentException('You cannot perform policy evaluations on a non-authenticated subject.');
        }
        $enforcer = new Enforcer();

        if ($policyName !== null) {
            // Find the policy by name
            $policy = $this->policies[$policyName];
            if ($policy === null) {
                throw new \InvalidArgumentException('Invalid policy: '.$policyName);
            }
            return $enforcer->evaluate($this->subject, $policy);
        } else {
            // evaluate all policies and combine using the type
            foreach ($this->policies as $policy) {
                $result = $enforcer->evaluate($this->subject, $policy);
                if ($type === Policy::ANY && $result === true) {
                    return true;
                }
                if ($type === Policy::ALL && $result === false) {
                    // Just one didn't pass, fail out
                    return false;
                }
            }
            return true;
        }
    }
}
