# Using a Class & Method for Evaluation

You can also use a class and method for evaluation as a part of `can` and `cannot` checks similar to how the closures work. Instead of passing in the closure method like in the previous examples, you simply pass in a string with the class and method names separated by a double colon (`::`). For example:

```php
<?php

$policy = Policy::instance()->can('\App\Foo::bar', [$post]);

?>
```

In this example, you're telling it to try to create an instance of the `\App\Foo` class and then try to call the `bar` method on that instance. *Note:* the method does not need to be static despite it using the double colon. Much like the closures, the subject will be passed in as the first parameter. Additional information will be passed in as other parameters following this.

So, in our above example the method would need to look like this:

```php
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