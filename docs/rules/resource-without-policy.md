# Resource without policy

Rule id: `resource-without-policy` · Default severity: **warning**

## What it catches

The resource's model has no policy registered, or the policy has no `viewAny()` method.

## Why it matters

Without a policy every authenticated user may view, create, edit and delete the records. With a policy that lacks `viewAny()`, Filament hides the resource from everyone and nothing explains why.

## How to fix

Create a policy with `php artisan make:policy` and register it (Laravel auto-discovers `App\Policies\{Model}Policy`). Always implement `viewAny()`.

## Turning it off

```php
'rules' => ['resource-without-policy' => 'off'],
```
