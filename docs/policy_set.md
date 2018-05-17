# Policy Sets

It's also possible to define a policy in a set, referenced by a key name (string). For example, if we wanted to create a simple policy that let a user with the username "testuser1" be able to perform an "edit post" action:

```php
<?php

$set = new PolicySet();
$set->add(
	'edit-post',
	Policy::instance()->hasUsername('testuser1')
);

// Or, using the instance method
$set = new PolicySet()
	->add('edit-post', Policy::instance()->hasUsername('testuser1'));
?>
```

Then, when we want to evaluate the user against this policy, we can use the `allows` and `denies` methods after injecting the set into the `Enforcer`:

```php
<?php
$myUser = (object)[
	'username' => 'testuser1',
	'permissions' => ['test1']
];
$enforcer = new Enforcer($set);

if ($enforcer->allows('edit-post', $myUser) === true) {
	echo 'Hey, you can edit the post - rock on!';
}

?>
```