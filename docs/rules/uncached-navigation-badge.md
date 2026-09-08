# Uncached navigation badge

Rule id: `uncached-navigation-badge` · Default severity: **warning**

## What it catches

`getNavigationBadge()` runs a query and no cache call appears in its body.

## Why it matters

The badge is computed on every page render for every user: one `COUNT(*)` per resource per request.

## How to fix

```php
public static function getNavigationBadge(): ?string
{
    return Cache::flexible('orders-badge', [30, 300], fn () => (string) Order::query()->pending()->count());
}
```

## Turning it off

```php
'rules' => ['uncached-navigation-badge' => 'off'],
```
