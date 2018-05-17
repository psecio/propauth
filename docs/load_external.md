# Loading Policies from an External Source

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