<?php

namespace Psecio\PropAuth\Authenticator;

class Password extends \Psecio\PropAuth\Authenticator
{
    public function evaluate()
    {
        $subject = $this->getContext('subject');
        $password = $this->getContext('password');

        if ($subject === null || $password === null) {
			throw new \Exception('Invalid user or password!');
		}

        // Compare the values
		return password_verify($password, $subject->password);
    }
}
