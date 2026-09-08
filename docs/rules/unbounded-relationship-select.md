# Unbounded relationship select

Rule id: `unbounded-relationship-select` · Default severity: **warning**

## What it catches

A `Select::relationship()` without `searchable()`.

## Why it matters

Every related row is loaded into the page on each render. Fine for ten countries, fatal for fifty thousand customers.

## How to fix

```php
Select::make('customer_id')->relationship('customer', 'name')->searchable()->preload()
```

Use `preload()` only when the list is small.

## Turning it off

```php
'rules' => ['unbounded-relationship-select' => 'off'],
```
