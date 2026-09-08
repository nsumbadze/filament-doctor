# Filament Doctor

Health checks for Filament panels. Doctor reads your resources, forms, tables, policies, importers and jobs and tells you what will bite your users before they find it:

- a translatable model whose Edit page forgot the `Translatable` concern and renders `[object Object]`,
- a `->searchable()` on a JSON column that scans the whole table on every keystroke,
- a resource with no policy, or a policy without `viewAny()` that silently hides the resource,
- a resource on a tenant panel whose model has no tenant relationship,
- translation keys that exist in no locale,
- permission names your policies check that nobody ever creates,
- relationship selects that load every row,
- navigation badges that run `COUNT(*)` on every render,
- `Filament::getTenant()` inside jobs, importers and exporters (always null there),
- importer columns that are not real attributes and crash `fillRecord()`.

It runs two ways: `php artisan filament:doctor` boots each panel and inspects it (the full rule set), and a PHPStan extension adds purely static rules to your existing analysis.

## Requirements

- PHP 8.2+
- Filament 4.x
- PHPStan 2.x for the static rules (optional)

## Installation

```bash
composer require --dev nsumbadze/filament-doctor
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=filament-doctor-config
```

## The command

```bash
php artisan filament:doctor                      # every panel, table output
php artisan filament:doctor --panel=admin        # one panel
php artisan filament:doctor --format=json        # machine-readable
php artisan filament:doctor --format=github      # annotations in GitHub Actions
php artisan filament:doctor --strict             # warnings fail too
php artisan filament:doctor --no-db              # skip checks that need a database
```

Exit code is `1` when any error-level finding remains, so it drops straight into CI.

```
 ✖ translatable-concern-missing (1) https://github.com/nsumbadze/filament-doctor/blob/main/docs/rules/translatable-concern-missing.md
   App\Filament\Resources\PaymentProviderResource\Pages\EditPaymentProvider
     Page "edit" of a translatable resource lacks the Translatable concern; forms will show [object Object].
     app/Filament/Resources/PaymentProviderResource/Pages/EditPaymentProvider.php:9

 ▲ uncached-navigation-badge (1) https://github.com/nsumbadze/filament-doctor/blob/main/docs/rules/uncached-navigation-badge.md
   App\Filament\Resources\OrderResource
     getNavigationBadge() queries the database on every render; wrap it in Cache::remember() or Cache::flexible().
     app/Filament/Resources/OrderResource.php:41

 1 error(s), 1 warning(s)
```

### Adopting on an existing project

Write every current finding to a baseline, fix things over time, and only new findings fail the build:

```bash
php artisan filament:doctor --generate-baseline   # writes filament-doctor-baseline.json
php artisan filament:doctor                       # reports only what is not in the baseline
php artisan filament:doctor --no-baseline         # everything again
```

Baseline entries are keyed by rule and subject, not by message, so wording changes between versions do not invalidate them.

## Rules

| Id | Default | What it catches |
| --- | --- | --- |
| [translatable-concern-missing](docs/rules/translatable-concern-missing.md) | error | Translatable model, resource or page without the `Translatable` concern |
| [json-column-searchable](docs/rules/json-column-searchable.md) | error | `searchable()` / `sortable()` on a JSON attribute |
| [resource-without-policy](docs/rules/resource-without-policy.md) | warning | No policy, or policy without `viewAny()` |
| [tenant-filter-missing](docs/rules/tenant-filter-missing.md) | error | Tenant panel resource whose model lacks the ownership relationship |
| [missing-translation-key](docs/rules/missing-translation-key.md) | warning | `__()` keys in resources and pages missing for a configured locale |
| [permission-name-drift](docs/rules/permission-name-drift.md) | error | Permission strings in policies that the permission store does not know |
| [unbounded-relationship-select](docs/rules/unbounded-relationship-select.md) | warning | `Select::relationship()` without `searchable()` |
| [uncached-navigation-badge](docs/rules/uncached-navigation-badge.md) | warning | `getNavigationBadge()` querying without a cache |
| [tenant-in-job](docs/rules/tenant-in-job.md) | error | `Filament::getTenant()` inside jobs, importers, exporters |
| [import-transient-column](docs/rules/import-transient-column.md) | error | Importer columns that are not model attributes without a `fillRecord()` override |

Change a severity or switch a rule off in the config:

```php
'rules' => [
    'json-column-searchable' => 'error',
    'uncached-navigation-badge' => 'off',
],
'ignore' => [
    App\Filament\Resources\LegacyResource::class,
],
```

### Writing your own rule

Implement `Nsumbadze\Doctor\Contracts\Rule` (or extend `Rules\AbstractRule`) and register it:

```php
'extra_rules' => [
    App\Doctor\NoRawHtmlColumnsRule::class,
],
```

The `Inspector` passed to your rule gives you the panel's resources, their evaluated form and table, model metadata, and helpers for scanning files.

## PHPStan rules

With `phpstan/extension-installer` the extension is picked up automatically. Otherwise:

```neon
includes:
    - vendor/nsumbadze/filament-doctor/extension.neon
```

Static rules shipped today:

- `filamentDoctor.tenantInJob` — `Filament::getTenant()` / `getTenantId()` inside a class that implements `ShouldQueue` or extends `Importer` / `Exporter`.
- `filamentDoctor.uncachedNavigationBadge` — `getNavigationBadge()` on a resource that queries without `Cache::` / `cache()` / `remember()` / `flexible()`.

The artisan command remains the complete rule set; the PHPStan half covers what can be decided without booting the application.

## Configuration reference

| Key | Purpose |
| --- | --- |
| `rules` | severity per rule: `error`, `warning`, `off` |
| `ignore` | classes never inspected |
| `paths.jobs` | directories scanned for tenant access (default: `app/Jobs`, `app/Filament/Imports`, `app/Filament/Exports`) |
| `paths.policies` | directories scanned for permission names |
| `paths.importers` | directories scanned for importer classes |
| `locales` | locales each translation key must exist in |
| `permissions` | `null` (Spatie Permission table when installed), an array, or a callable returning permission names |
| `baseline` | baseline file path |
| `extra_rules` | additional rule classes |

## Testing

```bash
composer test
composer analyse
composer format
```

## Licence

MIT.
