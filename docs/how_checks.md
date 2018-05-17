# How it checks properties

If you'll notice, we've called the `hasUsername` method on the `Policy` above despite it not being defined on the `User` class. This is handled by the `__call` magic method. It then looks for one of two key words: has or not. It determines which kind of check it needs to perform based on this.

- has: Tells the system to do an "equals" match, comparing the given value and the property value
- not: Tells the systems to do a "not equals" match

This gives you the flexibility to define custom policies based on the properties your user has and does not restrict it down to just a subset required by the object.

Your checks can have a second parameter after the value that further customizes the checks that it performs: `Policy::ANY` and `Policy:ALL`. These have different meanings based on the data in the property and the data defined. Here's a brief summary:

<style>
    td, th { padding: 5px; border: 1px solid #000000; }
    th { background-color: #EEEEEE; }
</style>

Property data type | Input Data type | ANY                                     | ALL
------------------ | --------------- | --------------------------------------- | -----------------------------------------------
string             | string          | equals (==)                             | equals (==)
string             | array           | input contains property                 | all input values equal property
array              | string          | property contains input                 | all property values equal input
array              | array           | any match of input with property values | property values exactly equals input

<br/>
> **NOTE:** The `Policy::ANY` rule is the default, regardless of data input type. All matches are done as *exactly* equal, even arrays (so if the order of the values is different they won't match).
