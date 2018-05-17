# Common Terms



## Policy
<a name="policy"></a>

A **policy** is a set of tests that can be run against data. In PropAuth, these policies are created using the `Policy` object. One or many checks are then appended to the `Policy` instance. 

For example, a policy that checks values for a user's first and last name my have the checks:

1. Has first name equal to "Chris"
2. Has last name equal to "Cornutt"

In PropAuth these tests are referred to as "checks".

## Policy Set
<a name="policyset"></a>

A **policy set** is pretty much what it sounds like: a set of `Policy` instances. Creating a policy set allows for easier grouping of multiple policies to make policies more reusable across the application.

## Enforcer
<a name="enforcer"></a>

The **enforcer** is the main entry point for the evaluation. It takes in the policy/policies to evaluate and the [subject](#subject) to match those policies against. It then returns a simple `pass` or `fail` status as a result of the evaluation.

## Resolver
<a name="resolver"></a>

The **resolver** is used when a "path" is provided to the [policy](#policy) to locate nested data without having to track it down yourself. For example, the path `permissions.tests` could refer to the `tests` property on each of the items in the [subject](#subject)'s `permissions` values.

## Subject
<a name="subject"></a>

The **subject** is the target of the evaluation. This could be an object or an array of data. It's passed into the [enforcer](#enforcer) at evaluation time and the checks are then run against it.

## Context

The **context** is the environment the evaluation lives in. The context could include the environemnt for the request, the resource being requested, or other related information.