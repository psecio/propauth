# Installation & Usage

Using the `PropAuth` library is simple and it can be installed along with your other dependencies with [Composer](https://getcomposer.org).

## Installation

You can install the library easily with Composer:

```bash
composer.phar install psecio/propauth
```

This will pull the library into your Composer `vendor/` directory. Currently the library has no other (non-dev) dependencies. If you're wanting to run the unit tests, you will need to install the [PHPUnit](https://phpunit.de/) dev dependency with the `--require-dev` command line flag.

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