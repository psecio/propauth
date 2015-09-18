<?php

namespace Psecio\PropAuth;

class Check
{
	private $type;
	private $value;
	private $addl = [];

	public function __construct($type, $value, array $addl = array())
	{
		$this->type = $type;
		$this->value = $value;
		$this->addl = $addl;
	}

	public function getType()
	{
		return $this->type;
	}
	public function getValue()
	{
		return $this->value;
	}
	public function getAddl()
	{
		return $this->addl;
	}
}