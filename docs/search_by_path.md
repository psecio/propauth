# Searching by "path"

PropAuth also allows you to search for the data to evaluate by a "path". Using this searching, you can recurse down through object and array data to locate just what you want rather than having to gather it before hand. Here's an example:

```php
<?php
class User
{
    public $permissions = [];
    public $username = ['user1', 'user2'];
}

class Perm
{
    public $name = '';
    protected $value = '';
    public $tests = [];

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    public function setTests(array $tests)
    {
        $this->tests = $tests;
    }

    public function getValue()
    {
        return $this->value;
    }
}
class Test
{
    public $title;
    public $foo = [];

    public function __construct($title)
    {
        $this->title = $title;
    }
    public function setFoos(array $foo)
    {
        $this->foo = $foo;
    }
}
class Foo
{
    public $test;

    public function __construct($test)
    {
        $this->test = $test;
    }
}

$policies = PolicySet::instance()
    ->add('test', Policy::instance()->find('permissions.tests.foo')->hasTest('t3'));

?>
```

In this case, we have a lot of nested data under the `User` instance that we want to evaluate. For this `test` policy, however, we only want one thing: the "test" values from the `Foo` objects. To fetch these we'd have to go through this process:

1. On the `User` instance, get the `permissions` property value
2. For each of the items in this set, get the `Test` instances that relate to them
3. Then, for each one of these tests, we want only the `Foo` instances related in a set.

While this can be done outside of the PropAuth library and just passed in directly for evaluation, the search "path" handling makes it easier. To perform all of the above, you just use the search path in the example above: `permissions.tests.foo`. This fetches all of the `Foo` instances then the `hasTest` method looks at the `test` value on the `Foo` objects to see if any match the `t3` value.