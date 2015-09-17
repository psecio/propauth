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
$myPolicy->hasUsername(['ccornutt', 'ccornutt1'], Policy::ANY);

$result = $enforcer->evaluate($myUser, $myPolicy);
echo 'RESULT: '.var_export($result, true)."\n\n"; // result is true

?>
```

