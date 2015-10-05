## PropAuth: Property-based policy evaluation

Performing evaluations on credentials for authentication or sets of permissions on users has its limitations. With these things you're restricted to evaluations like "has permission" or "credentials invalid". The goal behind `PropAuth` is to make these evaluations much more flexible and allow you to define reusable *policies* that can be evaluated against the provided user dynamically.

## Installation

You can install the library easily with Composer:

```bash
composer.phar install psecio/propauth
```

## Examples

```php
<?php

require_once 'vendor/autoload.php';

use \Psecio\PropAuth\Enforcer;
use \Psecio\PropAuth\Policy;

$enforcer = new Enforcer();
$myUser = (object)[
	'username' => 'ccornutt',
    'permissions' => ['test1'],
    'password' => password_hash('test1234', PASSWORD_DEFAULT)
];

$myPolicy = new Policy();
$myPolicy->hasUsername('ccornutt');

$result = $enforcer->evaluate($myUser, $myPolicy);
echo 'RESULT: '.var_export($result, true)."\n\n"; // result is true

// You can also chain the evaluations to make more complex policies
$myPolicy->hasUsername('ccornutt')->hasPermissions('test1'); // also true

// There's also a static method to help make the creation more concise
$myPolicy = Policy::instance()->hasUser('ccornutt')->hasPermissions('test1');

?>
```

> **NOTE:** All matching is treated as *AND* so **all** criteria must be true for the evaluation to pass.

## Allowed User Types

The PropAuth engine tries several different ways to get information from the user instance (properties) that should accomidate most of the common User class types out there. When checking for a property, the engine will, in this order:

- Look for a public property with the given name
- Look for a getter with the property name (ex: for "foo" it looks for "getFoo")
- Look for the "getProperty" method specifically and calls it with the property name

In the first code example, we're just creating a basic class (`stdClass`) and applying public properties so it would match with the first check for public properties.

## Verifying passwords

You can also use `PropAuth` to verify passwords as a part of your policy right along with the other evaluations. Here's an example of a policy that would verify the input for the user defined above:

```
<?php

$username = $_POST['username'];
$password = $_POST['password'];

$myPolicy = new Policy();
$myPolicy->hasUsername($username)->passwordEquals($password);

$result = $enforcer->evaluate($myUser, $myPolicy);
if ($result === true) {
    echo 'Valid login!';
}
?>
```

The password validation assumes the use of the [password hashing methods](http://php.net/manual/en/ref.password.php) and so requires PHP >=5.5 to function correctly. The plain-text password is given to the policy and hashed internally. Then the values are checked against the ones provided in the user for a match. In this case, if they put in either the wrong username or password, the policy evaluation will fail.

## How it checks properties

If you'll notice, we've called the `hasUsername` method on the `Policy` above despite it not being defined on the `User` class. This is handled by the `__call` magic method. It then looks for one of two key words: `has` or `not`. It determines which kind of check it needs to perform based on this.

- **has:** Tells the system to do an "equals" match, comparing the given value and the property value
- **not:** Tells the systems to do a "not equals" match

This gives you the flexibility to define custom policies based on the properties your user has and does not restrict it down to just a subset required by the object.

## Rules (`ANY` and `ALL`)

Your checks can have a secone parameter after the value that further customizes the checks that it performs: `Policy::ANY` and `Policy:ALL`. These ahve different meanings based on the data in the property and the data defined. Here's a brief summary:

Property data type | Input Data type | ANY                                     | ALL
------------------ | --------------- | --------------------------------------- | -----------------------------------------------
string             | string          | equals (==)                             | equals (==)
string             | array           | input contains property                 | all input values equal property
array              | string          | property contains input                 | all property values equal input
array              | array           | any match of input with property values | property values exactly equals input

> **NOTE:** The `Policy::ANY` rule is the default, regardless of data input type. All matches are done as *exactly* equal, even arrays (so if the order of the values is different they won't match).

## Other examples

**All of the following examples evaluate to `true` based on the defined user.**

```php
<?php

$policy = new Policy();

#### POSITIVE CHECKS

// Check to see if the permission is in a set
$user = new User([
	'permissions' => ['test1', 'test2']
]);
$policy->hasPermissions('test1');

// Check to see if any permissions match
$policy->hasPermissions(['test3', 'test2', 'test5'], Policy::ANY);

// Check to see that the pemrissions match exactly
$policy->hasPermissions(['test1', 'test2'], Policy::ALL);

#### NEGATIVE CHECKS

// Check to see if the permission is NOT in the set
$policy->notPermissions('test5');

// Check to see if NONE of the permissions match
$policy->notPermissions(['test3', 'test5', 'test6'], Policy::ANY);

// Check to see if the permissions are NOT equal
$policy->notPermissions(['test4', 'test5'], Policy::ALL);
?>

```

## Using Callbacks

If you have some more custom logic that you need to apply, you might want to use the callback handling built into PropAuth. Much like the "has" and "not" of the property checks, there's "can" (result should be true) and "cannot" (result should be false) for callbacks. Here's an example of each:

```php
<?php
// Make a user
$myUser = (object)[
	'username' => 'ccornutt',
	'permissions' => ['test1']
];

// Make a post
$post = (object)[
	'title' => 'This is a test post',
	'id' => 1
];

$myPolicy = new Policy();
$myPolicy
    ->hasUsername(['ccornutt', 'ccornutt1'], Policy::ANY)
    ->can(function($subject, $post) {
		return ($post->id === 1);
    })
    ->cannot(function($subject, $post) {
		return (strpos($post->title, 'foobar') === false);
    });

$result = $enforcer->evaluate($myUser, $myPolicy, [ $post ]); // result is TRUE
?>
```

> **NOTE:** The additional parameters that are passed in to the `evaluate` method will be given to the closure check types in the same order they're given in the array. However, the first paramater will *always* be the subject (User) being evaluated.

## Policy Sets

It's also possible to defile a policy in a set, referenced by a key name (string). For example, if we wanted to create a simple policy that let a user with the username "testuser1" be able to perform an "edit post" action:

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
