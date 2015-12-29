<?php

namespace Psecio\PropAuth;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the evaluation of a policy execution
     */
    public function testValidPolicyExecution()
    {
        $username = 'ccornutt';
        $user = (object)[
            'username' => $username
        ];
        $subject = new Subject($user);
        $subject->setAuth(true);

        $context = new Context([
            'policies' => [
                'test' => Policy::instance()->hasUsername($username)
            ]
        ]);
        $gateway = new Gateway($subject, $context);

        $result = $gateway->evaluate('test');
        $this->assertTrue($result);
    }

    /**
     * Test that an exception is thrown when a bad policy name
     * 	is given.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPolicyEvaluationBadName()
    {
        $subject = (object)['id' => 1];
        $context = new Context([]);

        $gateway = new Gateway($subject, $context);
        $gateway->evaluate('badpolicy');
    }

    /**
     * Test that an exception is thrown when you try to perform
     * 	the policy check on a non-authenticated subject
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPolicyEvaluationNonAuthed()
    {
        $subject = (object)['id' => 1];
        $context = new Context([
            'policies' => [ 'test' => Policy::instance()->hasUsername('ccornutt') ]
        ]);

        $gateway = new Gateway($subject, $context);
        $gateway->evaluate('test');
    }

    /**
     * Test the password evaluation handling on the Gateway class
     */
    public function testPasswordEvaluationValid()
    {
        $password = 'test1234';

        $subject = (object)[
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        $context = new Context([]);
        $gateway = new Gateway($subject, $context);

        $result = $gateway->authenticate($password);
        $this->assertInstanceOf('\Psecio\PropAuth\Subject', $result);
    }

    /**
     * Evaluate that things work correctly when a set is passed in, not
     * 	just an individual policy
     */
    public function testPolicySetupWithSet()
    {
        $set = PolicySet::instance()->add('policy1', Policy::instance()->hasUsername('ccornutt'));

        $user = (object)['username' => 'ccornutt'];
        $subject = new Subject($user);
        $subject->setAuth(true);

        $context = new Context([
            'policies' => $set
        ]);
        $gateway = new Gateway($subject, $context);

        // Evaluate the result of the policy above, true because they're:
        //  1. set correctly, 2. policy passes
        $result = $gateway->evaluate('policy1');
        $this->assertTrue($result);
    }
}
