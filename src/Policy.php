<?php

namespace Psecio\PropAuth;

class Policy
{
    private $checks = [];

    const ANY = 'any';
    const ALL = 'all';
    const NONE = 'none';

    private $keywords = [
    	'has', 'not'
    ];

    public function __call($name, $args)
    {
    	foreach ($this->keywords as $type) {
    		if (strpos($name, $type) === 0) {
    			$func = $type.'Check';
    			if (method_exists($this, $func)) {
    				$type = strtolower(str_replace($type, '', $name));
    				$this->$func($type, $name, $args);
    			}
    		}
    	}

        return $this;
    }

    /**
     * Catch the call to evaluate the password
     *
     * @param string $password Plain-text password input
     * @return \Psecio\PropAuth\Enforcer instance
     */
    public function passwordEquals($password)
    {
        $this->check('equals', 'password', 'passwordequals', [$password]);
        return $this;
    }

    private function check($matchType, $type, $name, $args)
    {
    	$value = array_shift($args);
        $matchType = 'equals';

        if (!isset($args[0])) {
            $args[0] = ['rule' => Policy::ANY];
        }

        // see what other options we've been given
        if (is_string($args[0]) && ($args[0] === Policy::ANY || $args[0] === Policy::ALL) ) {
            $args['rule'] = $args[0];
            unset($args[0]);
        } elseif (is_array($args[0])) {
            $args = $args[0];
        }

        $this->checks[$type][] = new Check($matchType, $value, $args);
    }

    private function hasCheck($type, $name, $args)
    {
    	$this->check('equals', $type, $name, $args);
    }
    private function notCheck($type, $name, $args)
    {
    	$this->check('not-equals', $type, $name, $args);
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
