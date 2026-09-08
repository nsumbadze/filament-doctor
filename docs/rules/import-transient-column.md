# Import transient column

Rule id: `import-transient-column` · Default severity: **error**

## What it catches

An importer declares an `ImportColumn` whose name is not a column of the model's table, the column is not a relationship, and the importer does not override `fillRecord()`.

## Why it matters

Filament's default `fillRecord()` assigns every mapped column onto the model, so the transient value ends up in the INSERT and the row fails with `column does not exist`.

## How to fix

Override `fillRecord()` (often as a no-op when `resolveRecord()` assigns everything) or map the column onto a real attribute / relationship.

## Turning it off

```php
'rules' => ['import-transient-column' => 'off'],
```
