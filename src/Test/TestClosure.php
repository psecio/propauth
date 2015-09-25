<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;

class TestClosure extends \Psecio\PropAuth\Test
{
	protected function evaluateEquals($value, $compare)
	{
		return ($this->executeClosure($value) === true) ? true : false;
	}
	protected function evaluateNotEquals($value, $compare)
	{
		return ($this->executeClosure($value) === false) ? true : false;
	}

	private function executeClosure($value)
	{
		$closure = $this->getTest()->getAddl()[0];
		return $closure($value);
	}
}