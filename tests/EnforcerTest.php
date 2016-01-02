<?php

namespace Psecio\PropAuth;

class EnforcerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getting of a value as a public property on the subject
     */
    public function testGetPropertyValuePublicProperty()
    {
        $username = 'ccornutt';
        $en = new Enforcer();
        $subject = new Subject((object)[
            'username' => $username
        ]);

        $result = $en->getPropertyValue('username', $subject);
        $this->assertEquals($username, $result);
    }

    /**
     * Test the getting of a value from a getter on the subject
     */
    public function testGetPeopertyValueMethod()
    {
        $firstname = 'Chris';
        $subject = new TestPropertySubject();
        $en = new Enforcer();

        $result = $en->getPropertyValue('firstname', $subject);
        $this->assertEquals($firstname, $result);
    }

    /**
     * Test the getting of a peoperty on the subject via a "getPropery" method
     */
    public function testGetPropertyGetPropertyMethod()
    {
        $lastname = 'Cornutt';
        $subject = new TestPropertySubject();
        $en = new Enforcer();

        $result = $en->getPropertyValue('lastname', $subject);
        $this->assertEquals($lastname, $result);
    }

    /**
     * Test that a warning is thrown when the policy is empty (no checks)
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testTriggerErrorWithoutPolicies()
    {
        $en = new Enforcer();
        $policy = Policy::instance();
        $subject = new Subject(['username' => 'ccornutt']);

        $result = $en->evaluate($subject, $policy);
    }

    /**
     * Test that a subject is correctly denined (the policy is not a match)
     */
    public function testSubjectIsDenied()
    {
        $policy = Policy::instance()->hasUsername('notthisuser');
        $policySet = PolicySet::instance()->add('policy1', $policy);

        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $this->assertTrue($en->denies('policy1', $subject));
    }

    /**
     * Test that, when the policy actually matches, the result of a
     * 	"denies" call is false
     */
    public function testSubjectDeniedInvalid()
    {
        $policy = Policy::instance()->hasUsername('ccornutt');
        $policySet = PolicySet::instance()->add('policy1', $policy);

        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $this->assertFalse($en->denies('policy1', $subject));
    }

    /**
     * Test that an exception is thrown when the policy is not in the set
     * 	on a "denies" call
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPolicyNameNotFoundDenies()
    {
        $policySet = PolicySet::instance();
        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $en->denies('policy1', $subject);
    }

    /**
     * Test that a subject is correctly allowed when the policy matches
     * 	on "allows" call
     */
    public function testAllowsSubjectValid()
    {
        $policy = Policy::instance()->hasUsername('ccornutt');
        $policySet = PolicySet::instance()->add('policy1', $policy);

        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $this->assertTrue($en->allows('policy1', $subject));
    }

    /**
     * Test that a false is returned when the user is not allowed
     * 	by the policy
     */
    public function testAllowsSubjectInvalid()
    {
        $policy = Policy::instance()->hasUsername('notrightuser');
        $policySet = PolicySet::instance()->add('policy1', $policy);

        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $this->assertFalse($en->allows('policy1', $subject));
    }

    /**
     * Test that an exception is thrown when teh policy name isn't found
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPolicyNameNotFoundAllows()
    {
        $policySet = PolicySet::instance();
        $subject = new Subject((object)['username' => 'ccornutt']);

        $en = new Enforcer($policySet);
        $en->allows('policy1', $subject);
    }
}

// Class for use in the tests above
class TestPropertySubject
{
    public function getFirstname()
    {
        return 'Chris';
    }
    public function getProperty($lastname)
    {
        return 'Cornutt';
    }
}
