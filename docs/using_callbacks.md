# Using Callbacks in Policies

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
