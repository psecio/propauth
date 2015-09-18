<?php

namespace Psecio\PropAuth;

class User
{
    protected $properties = [];

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->addProperty($name, $value);
        }
    }

    public function addProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function getProperty($name)
    {
        return (isset($this->properties[$name])) ? $this->properties[$name] : null;
    }
}
