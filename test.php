<?php

// property-based auth*

require_once 'User.php';
require_once 'Enforcer.php';
require_once 'Policy.php';
require_once 'Check.php';


// -------------------------
$enforcer = new Enforcer();
$myUser = new User([
    'username' => 'ccornutt',
    'permissions' => ['test1']
]);

$myPolicy = new Policy();
// $myPolicy->hasUsername('ccornutt')->notPermissions(['test']);
$myPolicy
    ->hasUsername(['ccornutt', 'ccornutt1'], Policy::ANY);
    // ->notUsername(['ccornutt', 'ccornutt2'], Policy::ANY);
    // ->notUsername(['ccornutt2'], Policy::ANY);
    // ->notPermissions(['test']);

print_r($myPolicy);

$result = $enforcer->evaluate($myUser, $myPolicy);
echo 'RESULT: '.var_export($result, true)."\n\n";

