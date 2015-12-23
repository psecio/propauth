<?php

namespace Psecio\PropAuth;

class Context
{
    protected $context = [];

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function get($key = null)
    {
        return (isset($this->context[$key])) ? $this->context[$key] : null;
    }
}
