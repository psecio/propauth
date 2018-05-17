
# Introduction

Performing evaluations on credentials for authentication or sets of permissions on users has its limitations. With these things you're restricted to evaluations like "has permission" or "credentials invalid". The goal behind PropAuth is to make these evaluations much more flexible and allow you to define reusable [policies](/terms.html#policy) that can be evaluated against the provided user dynamically.

## Quick Start

### Installation

You can install PropAuth via [Composer](https://getcomposer.org):

```sh
php composer.phar install psecio/propauth
```

### Example Usage

The following example sets up the `Enforcer` object and a `$myUser` object and uses the library to check and see if the user has a `username` property equal to "ccornutt":

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

?>

```