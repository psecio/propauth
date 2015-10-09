<?php

namespace Psecio\PropAuth\Test;
use Psecio\PropAuth\Policy;

class TestMethod extends \Psecio\PropAuth\Test
{
	/**
	 * Evaluate that the method result is false (not equals)
	 *
	 * @param string $value Value for evaluation
	 * @param string $compare Value to compare against
	 * @return boolean Pass/fail result of test (method result)
	 */
	protected function evaluateEquals($value, $compare)
	{
		return ($this->executeMethod($value, $compare) === true) ? true : false;
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
		return ($this->executeMethod($value, $compare) === false) ? true : false;
	}

	/**
	 * Execute the method on the class provided
	 *
	 * @param string $value Value for evaluation
	 * @param string $compare Value to compare against
	 * @return boolean Pass/fail result of test (method result)
	 */
	private function executeMethod($value, $compare)
	{
		$addl = $this->getAdditional();
		list ($class, $method) = explode('::', $value);

		// call the method with the test and subject
		if (!class_exists($class)) {
			throw new \InvalidArgumentException('Class "'.$class.'" does not exist');
		}
		$instance = new $class;

		if (!method_exists($instance, $method)) {
			throw new \InvalidArgumentException('Method "'.$method.'" does not exist on class "'.$class.'"');
		}
		return call_user_func_array([$instance, $method], $addl);
	}
}