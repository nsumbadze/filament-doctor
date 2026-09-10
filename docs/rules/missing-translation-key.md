# Missing translation key

Rule id: `missing-translation-key` · Default severity: **warning**

## What it catches

A resource or page calls `__()`, `trans()` or `trans_choice()` with a key that does not exist for one of the configured locales.

## Why it matters

The literal key (`validation.custom.sku`) shows up in the UI.

## How to fix

Add the key to every locale in `config('filament-doctor.locales')`. Keys without a dot or `::` are treated as plain sentences and ignored, as are keys built at runtime by concatenation or interpolation.

## Turning it off

```php
'rules' => ['missing-translation-key' => 'off'],
```
