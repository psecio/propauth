<?php

namespace Psecio\PropAuth;

abstract class Authenticator
{
    protected $context;

    public function __construct($context) {
        $this->context = $context;
    }
    public function getContext($name = null)
    {
        if ($name !== null) {
            return (isset($this->context[$name])) ? $this->context[$name] : null;
        } else {
            return $this->context;
        }
    }

    public abstract function evaluate();

    public static function make($type, $context)
    {
        $typeNs = '\\Psecio\\PropAuth\\Authenticator\\'.ucwords(strtolower($type));
        if (!class_exists($typeNs)) {
            throw new \InvalidArgumentException('Invalid authenticator type!');
        }
        return new $typeNs($context);
    }
}
