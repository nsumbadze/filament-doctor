<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Rule severities
    |--------------------------------------------------------------------------
    |
    | "error" fails the command (exit code 1), "warning" is reported only
    | (fails with --strict), "off" disables the rule.
    |
    */
    'rules' => [
        'translatable-concern-missing' => 'error',
        'json-column-searchable' => 'error',
        'resource-without-policy' => 'warning',
        'tenant-filter-missing' => 'error',
        'missing-translation-key' => 'warning',
        'permission-name-drift' => 'error',
        'unbounded-relationship-select' => 'warning',
        'uncached-navigation-badge' => 'warning',
        'tenant-in-job' => 'error',
        'import-transient-column' => 'error',

        // Raised when a resource's form or table could not be evaluated.
        'inspection-failed' => 'warning',
    ],

    /*
    | Classes (resources, pages, importers, jobs) that are never inspected.
    */
    'ignore' => [],

    /*
    | Skip resources whose class lives under vendor/ (shipped by other packages).
    */
    'ignore_vendor' => true,

    /*
    | Directories scanned by the file-based rules.
    */
    'paths' => [
        'jobs' => [app_path('Jobs'), app_path('Filament/Imports'), app_path('Filament/Exports')],
        'policies' => [app_path('Policies')],
        'importers' => [app_path('Filament/Imports')],
    ],

    /*
    | Locales every translation key must exist in.
    */
    'locales' => [config('app.locale', 'en')],

    /*
    | Where the permission names your policies check are expected to exist.
    | null => Spatie Permission's table when installed, otherwise the rule is skipped.
    | Or a callable returning a list of permission names.
    */
    'permissions' => null,

    /*
    | Additional rule classes implementing Nsumbadze\Doctor\Contracts\Rule.
    */
    'extra_rules' => [],

    /*
    | Baseline file: findings listed there are hidden from the report.
    */
    'baseline' => base_path('filament-doctor-baseline.json'),

];
