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
   * Adding a "can" check with a closure and checking it's correctly added
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

  /**
   * Adding a "not" check to a policy and checking it's correctly added
   */
  public function testAddNotCheck()
  {
    $policy = new Policy();
    $policy->notUsername('test');

    $verifyCheck = new Check('not-equals', 'test', ['rule' => Policy::ANY]);
    $checks = $policy->getChecks();

    $this->assertEquals($verifyCheck, $checks['username'][0]);
  }

  /**
   * Adding a "can" check using the class/method structure
   */
  public function testAddCanCheckClassMethod()
  {
    $policy = new Policy();
    $policy->can('TestClass::returnTrue');

    $checks = $policy->getChecks();
    $this->assertInstanceOf('\Psecio\PropAuth\Check', $checks['method'][0]);
  }

  /**
   * Adding a "cannot" check using the class/method structure
   */
  public function testAddCannotCheckClassMethod()
  {
    $policy = new Policy();
    $policy->cannot('TestClass::returnTrue');

    $checks = $policy->getChecks();
    $this->assertInstanceOf('\Psecio\PropAuth\Check', $checks['method'][0]);
  }

  /**
   * Adding a "cannot" check using the closure structure
   */
  public function testAddCannotCheckClosure()
  {
    $policy = new Policy();
    $policy->cannot(function($subject){ return false; });

    $checks = $policy->getChecks();
    $this->assertInstanceOf('\Psecio\PropAuth\Check', $checks['closure'][0]);
  }

  /**
   * Adding a "password" check type and verifying it's added correctly
   */
  public function testAddPasswordCheck()
  {
    $policy = new Policy();
    $policy->passwordEquals('test1234');
    $checks = $policy->getChecks();

    $this->assertTrue(isset($checks['password'][0]));
    $this->assertInstanceOf('\Psecio\PropAuth\Check', $checks['password'][0]);
  }

  /**
   * Adding a check with additional options (like the rule)
   */
  public function testAddCheckWithPolicy()
  {
    $policy = new Policy();
    $policy->hasUsername('test', ['rule' => Policy::ALL]);
    $checks = $policy->getChecks();

    $addl = $checks['username'][0]->getAddl();
    $this->assertEquals($addl['rule'], Policy::ALL);
  }
}
