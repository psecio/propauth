# Verifying passwords

You can also use PropAuth to verify passwords as a part of your policy right along with the other evaluations. Here's an example of a policy that would verify the input for the user defined above:

```php
<?php
$myUser = (object)[
    'username' => 'ccornutt',
    'password' => password_hash('test1234', PASSWORD_DEFAULT)
];

$gate = new Gateway($myUser);
$subject = $gate->authenticate($password);

if ($subject !== false && $subject->can('policy1') === true) {
	echo 'They can, woo!';
}

?>
```

The password validation assumes the use of the password hashing methods and so requires PHP >=5.5 to function correctly. The plain-text password is given to the policy and hashed internally. Then the values are checked against the ones provided in the user for a match. In this case, if they put in either the wrong username or password, the policy evaluation will fail.