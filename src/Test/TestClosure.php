<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;

class TestClosure extends \Psecio\PropAuth\Test
{
	/**
	 * Evaluate that the method result is true (not equals)
	 *
	 * @param string $value Value for evaluation
	 * @param string $compare Value to compare against
	 * @return boolean Pass/fail result of test (method result)
	 */
	protected function evaluateEquals($value, $compare)
	{
		return ($this->executeClosure($value) === true) ? true : false;
	}

	/**
	 * Evaluate that the method result is false (not equals)
	 *
	 * @param string $value Value for evaluation
	 * @param string $compare Value to compare against
	 * @return boolean Pass/fail result of test (method result)
	 */
	protected function evaluateNotEquals($value, $compare)
	{
		return ($this->executeClosure($value) === false) ? true : false;
	}

	/**
	 * Execute the closure, passing in the additional data as arguments
	 *
	 * @param Closure $value Closure to execute
	 * @return boolean Pass/fail of evaluation
	 */
	private function executeClosure($value)
	{
		$addl = $this->getAdditional();
		if (!is_array($addl)) {
			$addl = [$addl];
		}
		return call_user_func_array($value, $addl);
	}
}