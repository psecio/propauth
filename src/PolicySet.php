<?php

namespace Psecio\PropAuth;

class PolicySet implements \ArrayAccess, \Countable, \Iterator
{
	private $policies = [];
	private $position = 0;

	public function offsetExists($offset)
	{
		return isset($this->policies[$offset]);
	}
	public function offsetGet($offset)
	{
		return (isset($this->policies[$offset])) ? $this->policies[$offset] : null;
	}
	public function offsetSet($offset, $value)
	{
		$this->policies[$offset] = $value;
	}
	public function offsetUnset($offset)
	{
		unset($this->policies[$offset]);
	}

	public function current()
	{
		$slice = array_slice($this->policies, $this->position, 1);
		return array_shift($slice);
	}
	public function key()
	{
		$slice = array_slice($this->policies, $this->position, 1);
		return array_keys($slice)[0];
	}
	public function next()
	{
		++$this->position;
	}
	public function rewind()
	{
		$this->position = 0;
	}
	public function valid()
	{
		return ($this->position <= count($this->policies) - 1);
	}

	public function count()
	{
		return count($this->policies);
	}

	public function add($policyName, \Psecio\PropAuth\Policy $policy)
	{
		$this->policies[$policyName] = $policy;
	}
}