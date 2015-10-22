<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;
use \Psecio\PropAuth\Exception\MissingParametersException;

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
	 * @throws \Psecio\PropAuth\Exception\MissingParametersException If not enough params were given for the closure
	 * @return boolean Pass/fail of evaluation
	 */
	private function executeClosure($value)
	{
		$addl = $this->getAdditional();
		if (!is_array($addl)) {
			$addl = [$addl];
		}
		// Inspect the closure and ensure we have enough parameters
		$info = new \ReflectionFunction($value);
		$required = $info->getNumberOfParameters();
		if (count($addl) < $required) {
			// Here we subtract 1 because the first param (subject) is forcefully injected
			throw new MissingParametersException('Not enough parameters provided for the check. ('.($required-1).' required)');
		}

		return call_user_func_array($value, $addl);
	}
}