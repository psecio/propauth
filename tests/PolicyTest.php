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

  /**
   * Test the loading of a policy stright and the resulting
   * 	policy set and objects
   *
   * @covers \Psecio\PropAuth\Policy::load
   */
  public function testLoadFromString()
  {
      $string = 'hasUsername:ccornutt||notUsername:ccornutt1||hasPermissions:(test1,test2)[ANY]';

      $policy = Policy::load($string);
      $checks = $policy->getChecks();

      // Ensure they were both set
      $this->assertTrue(isset($checks['username']) && isset($checks['permissions']));

      // Vreify the checks are set correctly
      $ptest1 = Policy::instance()->hasUserName('ccornutt');
      $this->assertEquals($checks['username'][0], $ptest1->getChecks()['username'][0]);

      $ptest2 = Policy::instance()->hasPermissions(['test1', 'test2'], Policy::ANY);
      $this->assertEquals($checks['permissions'][0], $ptest2->getChecks()['permissions'][0]);
  }

  /**
   * Test the creation of a new Policy object with the instance method
   */
  public function testCreateNewInstance()
  {
      $policy = Policy::instance();
      $this->assertInstanceOf('\Psecio\PropAuth\Policy', $policy);
  }
}
