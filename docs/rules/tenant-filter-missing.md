# Tenant filter missing

Rule id: `tenant-filter-missing` · Default severity: **error**

## What it catches

On a panel with tenancy, a resource is scoped to the tenant but its model has no ownership relationship, or the resource opted out of scoping without overriding `getEloquentQuery()`.

## Why it matters

Either Filament throws when it tries to scope the query, or every tenant sees every record.

## How to fix

Add the relationship named by the panel's `ownershipRelationship` (default `owner` or the tenant model name) to the model, or set `protected static ?string $tenantOwnershipRelationshipName` on the resource, or override `getEloquentQuery()` with an explicit filter.

## Turning it off

```php
'rules' => ['tenant-filter-missing' => 'off'],
```
