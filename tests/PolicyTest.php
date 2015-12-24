<?php

namespace Psecio\PropAuth;

class PolicyTest extends \PHPUnit_Framework_TestCase
{
  public function testAddCheckMagicMethod()
  {
    $policy = new Policy();
    $policy->hasUsername('test');

    $verifyCheck = new Check('equals', 'test', ['rule' => Policy::ANY]);
    $checks = $policy->getChecks();

    $this->assertEquals($verifyCheck, $checks['username'][0]);
  }

  /**
   * Adding a "can" check with a closure
   */
  public function testAddCheckClosure()
  {
    $policy = new Policy();
    $policy->can(function($subject) {
      return true;
    });

    $checks = $policy->getChecks();
    $this->assertInstanceOf('\Psecio\PropAuth\Check', $checks['closure'][0]);
  }
}
