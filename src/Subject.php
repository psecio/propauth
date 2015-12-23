<?php

namespace Psecio\PropAuth;

class Subject
{
    protected $subject;
    protected $authStatus = 0;

    const AUTH_STATUS_NONE = 0;
    const AUTH_STATUS_VALID = 1;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function __get($name)
    {
        return $this->subject->$name;
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
}
