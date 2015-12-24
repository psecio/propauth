<?php

namespace Psecio\PropAuth;

class Policy
{
    /**
     * Current list of checks on the Policy instance
     * @var array
     */
    private $checks = [];

    /**
     * Match type constants
     */
    const ANY = 'any';
    const ALL = 'all';
    const NONE = 'none';

    /**
     * Match keywords
     * @var array
     */
    private $keywords = [
    	'has', 'not'
    ];

    /**
     * Exact type matches
     * @var array
     */
    private $exact = [
        'cannot', 'can'
    ];

    /**
     * Magic method to catch policy additions on the current instance
     *
     * @param string $name Method name
     * @param array $args Method arguments
     * @return \Psecio\PropAuth\Policy instance
     */
    public function __call($name, $args)
    {
    	foreach ($this->keywords as $type) {
    		if (strpos($name, $type) === 0) {
                $this->addCheck($name, $type, $args);
    		}
    	}
        if (in_array($name, $this->exact)) {
            $this->addCheck($name, $name, $args);
        }

        return $this;
    }

    /**
     * Add a new check to the set
     *
     * @param string $name Function name
     * @param string $type Check type
     * @param array $args Check arguments
     * @throws \InvalidArgumentException If check type is invalid
     */
    protected function addCheck($name, $type, $args)
    {
        $func = $type.'Check';

        if (method_exists($this, $func)) {
            $type = strtolower(str_replace($type, '', $name));
            $this->$func($type, $name, $args);
        } else {
          throw new \InvalidArgumentException('Invalid check type: '.$type);
        }
    }

    /**
     * Static method to return a new Policy instance
     *
     * @return \Psecio\PropAuth\Policy
     */
    public static function instance()
    {
        return new Policy();
    }

    /**
     * Load a policy from a string DSL
     *     See the README for formatting
     *
     * @param string $dslString DSL formatted string
     */
    public static function load($dslString)
    {
        $policy = self::instance();
        $parts = explode('||', $dslString);

        foreach ($parts as $match) {
            $args = [];
            list($method, $value) = explode(':', $match);

            // if we have data following the ":" inside (), array it
            if (preg_match('/:\((.+?)\)/', $match, $matches) > 0) {
                if (isset($matches[1])) {
                    $value = explode(',', $matches[1]);
                }
            }
            $args[] = $value;

            // Finally, see if we have a modifier
            if (preg_match('/\[(.+?)\]$/', $match, $matches) > 0) {
                if (isset($matches[1])) {
                    $args[] = strtolower($matches[1]);
                }
            }

            call_user_func_array([$policy, $method], $args);
        }

        return $policy;
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

    /**
     * Add a new check to the current set for the policy
     *
     * @param string $matchType Match type
     * @param string $type Type of check
     * @param string $name Name for the check
     * @param array $args Additional arguments for the check
     */
    private function check($matchType, $type, $name, $args)
    {
    	$value = array_shift($args);

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

    /**
     * Add a "has" check to the set
     *
     * @param string $type Check type
     * @param string $name Name for the check
     * @param array $args Additional arguments
     */
    private function hasCheck($type, $name, $args)
    {
    	$this->check('equals', $type, $name, $args);
    }

    /**
     * Add a "can" check to the set
     *
     * @param string $type Check type
     * @param string $name Name for the check
     * @param array $args Additional arguments
     */
    private function canCheck($type, $name, $args)
    {
        if (isset($args[0]) && (is_object($args[0]) && get_class($args[0]) === 'Closure')) {
            $type = 'closure';
            $args[1] = $args[0];
        } elseif (is_string($args[0]) && strpos($args[0], '::') !== false) {
            $type = 'method';
        }
        $this->check('equals', $type, $name, $args);
    }

    /**
     * Add a "cannot" check to the set
     *
     * @param string $type Check type
     * @param string $name Name for the check
     * @param array $args Additional arguments
     */
    private function cannotCheck($type, $name, $args)
    {
        if (isset($args[0]) && (is_object($args[0]) && get_class($args[0]) === 'Closure')) {
            $type = 'closure';
            $args[1] = $args[0];
        } elseif (is_string($args[0]) && strpos($args[0], '::') !== false) {
            $type = 'method';
        }

        $this->check('not-equals', $type, $name, $args);
    }

    /**
     * Add a "not" check to the set
     *
     * @param string $type Check type
     * @param string $name Name for the check
     * @param array $args Additional arguments
     */
    private function notCheck($type, $name, $args)
    {
    	$this->check('not-equals', $type, $name, $args);
    }

    /**
     * Get the full list of current checks on the policy
     *
     * @return array Set of current checks
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
