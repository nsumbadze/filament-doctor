# Filament Doctor

An audit command and a set of PHPStan rules for Filament panels.

`php artisan filament:doctor` boots each panel, inspects its resources, forms, tables, policies, importers and jobs, and reports problems that otherwise only show up in production. The PHPStan extension covers the subset of checks that can be made without booting the application.

## Requirements

- PHP 8.2 or newer (8.3 for Filament 5)
- Filament 4 or 5
- PHPStan 2 for the static rules (optional)

## Installation

```bash
composer require --dev nsumbadze/filament-doctor
```

The config file can be published with `php artisan vendor:publish --tag=filament-doctor-config`.

## Command

```bash
php artisan filament:doctor                 # all panels
php artisan filament:doctor --panel=admin
php artisan filament:doctor --format=json
php artisan filament:doctor --format=github # GitHub Actions annotations
php artisan filament:doctor --strict        # warnings also fail
php artisan filament:doctor --no-db         # skip checks that need a database connection
```

The exit code is 1 when at least one error-level finding remains.

```
 ✖ translatable-concern-missing (1)
   App\Filament\Resources\PaymentProviderResource\Pages\EditPaymentProvider
     Page "edit" of a translatable resource lacks the Translatable concern; forms will show [object Object].
     app/Filament/Resources/PaymentProviderResource/Pages/EditPaymentProvider.php:9

 ▲ uncached-navigation-badge (1)
   App\Filament\Resources\OrderResource
     getNavigationBadge() queries the database on every render; wrap it in Cache::remember() or Cache::flexible().
     app/Filament/Resources/OrderResource.php:41

 1 error(s), 1 warning(s)
```

### Baseline

An existing project can record its current findings and only fail on new ones:

```bash
php artisan filament:doctor --generate-baseline   # writes filament-doctor-baseline.json
php artisan filament:doctor                       # findings in the baseline are hidden
php artisan filament:doctor --no-baseline
```

Entries are keyed by rule and subject, not by message, and file paths are relative to the project root, so the file works across machines.

## Rules

| Rule | Default | Reports |
| --- | --- | --- |
| [translatable-concern-missing](docs/rules/translatable-concern-missing.md) | error | A translatable model whose resource or page does not use the `Translatable` concern |
| [json-column-searchable](docs/rules/json-column-searchable.md) | error | `searchable()` or `sortable()` on a JSON attribute |
| [resource-without-policy](docs/rules/resource-without-policy.md) | warning | No policy, or a policy without `viewAny()` |
| [tenant-filter-missing](docs/rules/tenant-filter-missing.md) | error | A resource on a tenant panel whose model has no ownership relationship |
| [missing-translation-key](docs/rules/missing-translation-key.md) | warning | Translation keys used in resources and pages that a configured locale does not define |
| [permission-name-drift](docs/rules/permission-name-drift.md) | error | Permission names checked in policies that do not exist in the permission store |
| [unbounded-relationship-select](docs/rules/unbounded-relationship-select.md) | warning | `Select::relationship()` without `searchable()` |
| [uncached-navigation-badge](docs/rules/uncached-navigation-badge.md) | warning | `getNavigationBadge()` that queries the database without a cache |
| [tenant-in-job](docs/rules/tenant-in-job.md) | error | `Filament::getTenant()` inside jobs, importers or exporters |
| [import-transient-column](docs/rules/import-transient-column.md) | error | Importer columns that are not model attributes while `fillRecord()` is not overridden |

Each rule has a page under `docs/rules` describing what it reports and how to fix it.

The file-scanning rules read source line by line and skip comment lines. A finding produced by more than one panel is reported once.

Severities are set per rule in the config file. `off` disables a rule.

```php
'rules' => [
    'json-column-searchable' => 'error',
    'uncached-navigation-badge' => 'off',
],

'ignore' => [
    App\Filament\Resources\LegacyResource::class,
],
```

### Custom rules

Implement `Nsumbadze\Doctor\Contracts\Rule`, or extend `Nsumbadze\Doctor\Rules\AbstractRule`, and add the class to `extra_rules`. The `Inspector` passed to `check()` provides the panel's resources, their evaluated form and table, model metadata and file scanning helpers.

```php
use Filament\Panel;
use Nsumbadze\Doctor\Rules\AbstractRule;
use Nsumbadze\Doctor\Support\Inspector;

class NoHtmlColumnsRule extends AbstractRule
{
    public function id(): string
    {
        return 'no-html-columns';
    }

    public function description(): string
    {
        return 'Table columns rendering unescaped HTML';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            foreach ($inspector->table($resource)?->getColumns() ?? [] as $column) {
                if ($column->isHtml()) {
                    yield $this->finding("{$resource}::{$column->getName()}", 'Column renders raw HTML.', $resource);
                }
            }
        }
    }
}
```

## PHPStan rules

The extension is registered automatically with `phpstan/extension-installer`. Otherwise:

```neon
includes:
    - vendor/nsumbadze/filament-doctor/extension.neon
```

| Identifier | Reports |
| --- | --- |
| `filamentDoctor.tenantInJob` | `Filament::getTenant()` or `getTenantId()` in a class that implements `ShouldQueue` or extends `Importer` or `Exporter` |
| `filamentDoctor.uncachedNavigationBadge` | `getNavigationBadge()` on a resource that queries without `Cache::`, `cache()`, `remember()` or `flexible()` |

## Configuration

| Key | Description |
| --- | --- |
| `rules` | Severity per rule: `error`, `warning` or `off` |
| `ignore` | Classes that are never inspected |
| `paths.jobs` | Directories scanned for tenant access. Default: `app/Jobs`, `app/Filament/Imports`, `app/Filament/Exports` |
| `paths.policies` | Directories scanned for permission names |
| `paths.importers` | Directories scanned for importer classes |
| `locales` | Locales every translation key must exist in |
| `permissions` | `null` reads Spatie Permission's table when installed. An array or callable supplies the names directly. |
| `baseline` | Path of the baseline file |
| `extra_rules` | Additional rule classes |

## Testing

```bash
composer test
composer analyse
composer format
```

## License

MIT. See [LICENSE.md](LICENSE.md).
