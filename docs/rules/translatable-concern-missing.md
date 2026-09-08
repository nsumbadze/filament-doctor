# Translatable concern missing

Rule id: `translatable-concern-missing` · Default severity: **error**

## What it catches

A model uses Spatie's `HasTranslations` but the resource, or one of its pages, does not use the `Translatable` concern from the Filament translatable plugin (official or Lara Zeus).

## Why it matters

The form binds the raw translations array into a plain input, so the field renders `[object Object]` and saving overwrites every locale with that string. List pages without the concern search and sort the JSON blob.

## How to fix

Add the concern to the resource and to every Create, Edit, List and View page:

```php
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPaymentProvider extends EditRecord
{
    use Translatable;
}
```

## Turning it off

```php
'rules' => ['translatable-concern-missing' => 'off'],
```
