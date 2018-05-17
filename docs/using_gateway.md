# Using the Gateway interface for evaluation

In addition to the powerful features already listed here, the `PropAuth` library also provides a simplified interface for working with your user (subject) and its authentication and authorization.

First, let's take a look at authentication. It uses a `bcrypt` hashing method behind the scenes to evaluate the password:

```php
<?php
$myUser = (object)[
    'username' => 'ccornutt',
    'password' => password_hash('test1234', PASSWORD_DEFAULT)
];

$gate = new Gateway($myUser);
$subject = $gate->authenticate($password);

// Then we can check if the user is authenticated
echo 'Is authenticated? '.var_export($subject->isAuthed(), true)."\n";

?>
```

We create the `Gateway` class instance and then can call the `authenticate` method on it with the password provided. The script then assumes it can access the user's `password` property as a value on the object and makes a comparison. It will return a new instance of a `Subject` class if the authentication is successful or `false` if not. The `Subject` class is just a wrapper around your object (`$myUser` in this case). The original object can be fetched using the `Subject->getSubject()` method.

Additionally, you can provide a bit more context and use the `Gateway` interface for policy evaluation too. You simply define the policies as a part of the `Context` object in the constructor:

```php
<?php
$context = new Context([
	'policies' => [
		'policy1' => Policy::instance()->hasUsername('ccornutt')
	]
]);
$gate = new Gateway($myUser, $context);

// When we can call the "evaluate" method with the policies we want to check:
if ($gate->evaluate('policy1') === true) {
	echo 'Policy1 passes!';
}

// Or you can just add your own PolicySet instance and use "evaluate" the same way
$myPolicySet = new PolicySet()->add('edit-post', Policy::instance()->hasUsername('testuser1'));

$context = new Context([
	'policies' => $myPolicySet
]);

?>
```

Once you have your valid `Subject` instance, you can then check its abilities with the `can` and `cannot` methods:

```php
<?php
$myUser = (object)[
    'username' => 'ccornutt',
    'password' => password_hash('test1234', PASSWORD_DEFAULT)
];

$context = new Context([
	'policies' => [
		'policy1' => Policy::instance()->hasUsername('ccornutt')
	]
]);

$gate = new Gateway($myUser, $context);
$subject = $gate->authenticate($password);

if ($subject !== false && $subject->can('policy1') === true) {
	echo 'They can, woo!';
}

?>
```

The parameter on the `can` and `cannot` methods are policy names you've already defined in your context. If the `Policy` is defined as a closure with more complex logic, you can provide this option (or multiple options) as the second parameter:

```php
<?php
$post = (object)[
	'author' => 'ccornutt'
];
$set = PolicySet::instance()->add(
    'delete',
    Policy::instance()->can(function($subject, $post) {
        return ($subject->username == 'ccornutt' && $post->author == 'ccornutt');
    })
);
$context = new Context(['policies' => $set]);
$gate = new Gateway($myUser, $context);

$subject = $gate->authenticate('test1234');

if ($subject->can('delete', $post) === true) {
	echo 'They can delete it!';
}
?>
```