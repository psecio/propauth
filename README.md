## PropAuth: Property-based policy evaluation

[![Travis-CI Build Status](https://secure.travis-ci.org/psecio/propauth.png?branch=master)](http://travis-ci.org/psecio/propauth)

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

The PropAuth engine tries several different ways to get information from the user instance (properties) that should accommodate most of the common User class types out there. When checking for a property, the engine will, in this order:

- Look for a public property with the given name
- Look for a getter with the property name (ex: for "foo" it looks for "getFoo")
- Look for the "getProperty" method specifically and calls it with the property name

In the first code example, we're just creating a basic class (`stdClass`) and applying public properties so it would match with the first check for public properties.

## Verifying passwords

You can also use `PropAuth` to verify passwords as a part of your policy right along with the other evaluations. Here's an example of a policy that would verify the input for the user defined above:

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

The password validation assumes the use of the [password hashing methods](http://php.net/manual/en/ref.password.php) and so requires PHP >=5.5 to function correctly. The plain-text password is given to the policy and hashed internally. Then the values are checked against the ones provided in the user for a match. In this case, if they put in either the wrong username or password, the policy evaluation will fail.

## How it checks properties

If you'll notice, we've called the `hasUsername` method on the `Policy` above despite it not being defined on the `User` class. This is handled by the `__call` magic method. It then looks for one of two key words: `has` or `not`. It determines which kind of check it needs to perform based on this.

- **has:** Tells the system to do an "equals" match, comparing the given value and the property value
- **not:** Tells the systems to do a "not equals" match

This gives you the flexibility to define custom policies based on the properties your user has and does not restrict it down to just a subset required by the object.

## Rules (`ANY` and `ALL`)

Your checks can have a second parameter after the value that further customizes the checks that it performs: `Policy::ANY` and `Policy:ALL`. These have different meanings based on the data in the property and the data defined. Here's a brief summary:

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

// Check to see that the permissions match exactly
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

> **NOTE:** The additional parameters that are passed in to the `evaluate` method will be given to the closure check types in the same order they're given in the array. However, the first parameter will *always* be the subject (User) being evaluated.

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

## Using a Class & Method for Evaluation

You can also use a class and method for evaluation as a part of `can` and `cannot` checks similar to how the closures work. Instead of passing in the closure method like in the previous examples, you simply pass in a string with the class and method names separated by a double colon (`::`). For example:

```
<?php

$policy = Policy::instance()->can('\App\Foo::bar', [$post]);

?>
```

In this example, you're telling it to try to create an instance of the `\App\Foo` class and then try to call the `bar` method on that instance. *Note:* the method does not need to be static despite it using the double colon. Much like the closures, the subject will be passed in as the first parameter. Additional information will be passed in as other parameters following this.

So, in our above example the method would need to look like this:

```
<?php
namespace App;

class Foo
{
	public function foo($subject, $post)
	{
		/* evaluation here, return boolean */
	}
}

?>
```

## Loading Policies from an External Source

Defining policies in your code is good, but sometimes it just makes more sense to have them in an external location where you can load them as needed. Maybe you have a situation where only "Post" related policies need to be loaded and not everything across the entire site. `PropAuth` offers a "load DSL" method on the `Policy` class that can help here.

What's a DSL? A "domain specific language" lets you define a string in a certain format where `PropAuth` will understand how to parse it and create a policy instance from it. Let's start with an example and then break down the format:

```
hasUsername:ccornutt||notUsername:ccornutt1||hasPermissions:(test1,test2)[ANY]
```

You'll notice that there's similarities in the method names you'd call if you were making the policy yourself and what the DSL will recognize (like `hasUsername`). This DSL string defines the following policy:

- subject has the username of "ccornutt"
- subject does not have the username of "ccornutt1"
- subject has any of the following permissions: test1, test2

The logic is the same as if you were manually setting those methods on the policy object, just in a simplified way. Here's how it looks in code:

```php
<?php
$dsl = 'hasUsername:ccornutt||notUsername:ccornutt1||hasPermissions:(test1,test2)[ANY]';
$myPolicy = Policy::load($dsl);
?>
```

Simple right? Okay, so lets look at the format:

- the method/value pairs are split by the double pipe (`||`)
- the method name and value are then split by a colon (`:`)
- single string values are just put into the string, arrays are surrounded by parentheses and split with a comma
- modifiers (like `ANY` or `ALL`) are added to the end of the method/value pair, enclosed in brackets (`[]`)

Obviously this is only really for simpler checks where the criteria can be defined by strings and simple values, but it can be quite useful for a wide range of circumstances. Of course, you can always pull these as base policies and then add on to them manually once you have the `Policy` object created post-load.

## Using the Gateway interface for evaluation

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
