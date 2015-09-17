## Property-based auth*

Example:

```php
<?php

$enforcer = new Enforcer();
$myUser = new User([
    'username' => 'ccornutt',
    'permissions' => ['test1']
]);

$myPolicy = new Policy();
$myPolicy->hasUsername('ccornutt');

$result = $enforcer->evaluate($myUser, $myPolicy);
echo 'RESULT: '.var_export($result, true)."\n\n"; // result is true

// You can also chain the evaluations to make more complex policies
$myPolicy->hasUsername('ccornutt')->hasPermissions('test1'); // also true
?>
```

### Creating the User

The `User` class is designed to take in a set of `properties` as an array in the constructor (as seen above). These properties them are used in the evaluation process. The names of the properties are important as they're used for the "magic" has/not checks.

### How it checks properties

If you'll notice, we've called the `hasUsername` method on the `Policy` above despite it not being defined on the `User` class. This is handled by the `__call` magic method. It then looks for one of two key words: `has` or `not`. It determines which kind of check it needs to perform based on this.

- **has:** Tells the system to do an "equals" match, comparing the given value and the property value
- **not:** Tells the systems to do a "not equals" match

This gives you the flexibility to define custom policies based on the properties your user has and does not restrict it down to just a subset required by the object.

### Rules (`ANY` and `ALL`)

Your checks can have a secone parameter after the value that further customizes the checks that it performs: `Policy::ANY` and `Policy:ALL`. These ahve different meanings based on the data in the property and the data defined. Here's a brief summary:

Property data type | Input Data type | ANY                                     | ALL
------------------ | --------------- | --------------------------------------- | -----------------------------------------------
string             | string          | equals (==)                             | equals (==)
string             | array           | input contains property                 | all input values equal property
array              | string          | property contains input                 | all property values equal input
array              | array           | any match of input with property values | property values exactly equals input

> **NOTE:** The `Policy::ANY` rule is the default, regardless of data input type. All matches are done as *exactly* equal, even arrays (so if the order of the values is different they won't match).

### Other examples

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
