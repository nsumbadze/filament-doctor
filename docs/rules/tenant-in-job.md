# Tenant in job

Rule id: `tenant-in-job` · Default severity: **error**

## What it catches

`Filament::getTenant()` or `Filament::getTenantId()` is referenced inside a job, importer or exporter.

## Why it matters

The tenant is resolved from the HTTP request. Queued code has no request, so the call returns `null` and the code silently operates on no tenant, or on all of them.

## How to fix

Pass the tenant explicitly: through the job constructor, or through the import/export action `->options(['tenant_id' => …])` and read it back from `$this->options`.

## Turning it off

```php
'rules' => ['tenant-in-job' => 'off'],
```
