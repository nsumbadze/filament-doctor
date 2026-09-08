# Permission name drift

Rule id: `permission-name-drift` · Default severity: **error**

## What it catches

A policy checks a permission name (`$user->can('create_post')`) that the permission store does not contain.

## Why it matters

The check fails for everybody, forever, without any error. Typical cause: a generator that emits `Create:Post` while the policy was written for `create_post`.

## How to fix

Rename either side. The store is Spatie Permission's table when installed, or configure `permissions` with an array or a callable returning the known names.

## Turning it off

```php
'rules' => ['permission-name-drift' => 'off'],
```
