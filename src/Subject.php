<?php

namespace Psecio\PropAuth;

class Subject
{
    protected $subject;
    protected $authStatus = 0;
    protected $policies;

    const AUTH_STATUS_NONE = 0;
    const AUTH_STATUS_VALID = 1;

    public function __construct($subject, $policies = null)
    {
        $this->subject = $subject;

        if ($policies !== null) {
            $this->policies = $policies;
        }
    }

    public function __get($name)
    {
        return $this->subject->$name;
    }

    public function __isset($name)
    {
        return (isset($this->subject->$name));
    }

    public function setAuth($status)
    {
        $this->authStatus = ($status == true) ? self::AUTH_STATUS_VALID : self::AUTH_STATUS_NONE;
    }

    public function isAuthed()
    {
        return ($this->authStatus === self::AUTH_STATUS_VALID);
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setPolicies(PolicySet $policySet)
    {
        $this->policies = $policySet;
    }

    public function can($policyName, $addl = null)
    {
        if ($addl !== null) {
            $addl = (!is_array($addl)) ? [$addl] : $addl;
        }
        $policy = $this->policies[$policyName];
        if ($policy === null) {
            throw new \InvalidArgumentException('Invalid policy name: '.$policyName);
        }
        $enforcer = new Enforcer();
        return $enforcer->evaluate($this->subject, $policy, $addl);
    }

    public function cannot($policyName, $addl = null)
    {
        if ($addl !== null) {
            $addl = (!is_array($addl)) ? [$addl] : $addl;
        }
        $policy = $this->policies[$policyName];
        if ($policy === null) {
            throw new \InvalidArgumentException('Invalid policy name: '.$policyName);
        }
        $enforcer = new Enforcer();
        return !($enforcer->evaluate($this->subject, $policy, $addl));
    }
}
