<?php

declare(strict_types=1);

use Nsumbadze\Doctor\Doctor;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;
use Nsumbadze\Doctor\Tests\Fixtures\Imports\ProductImporter;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\NoteResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource\Pages\EditPost;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource\Pages\ListPosts;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\ProductResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource;

beforeEach(function (): void {
    $this->findings = app(Doctor::class)->run('admin');
});

it('flags a translatable resource page without the Translatable concern', function (): void {
    $found = findingsFor($this->findings, 'translatable-concern-missing');

    expect(array_map(fn (Finding $f): string => $f->subject, $found))->toBe([EditPost::class])
        ->and($found[0]->severity)->toBe(Severity::Error)
        ->and($found[0]->file)->toEndWith('EditPost.php');

    expect(findingsFor($this->findings, 'translatable-concern-missing', ListPosts::class))->toBeEmpty();
});

it('flags searchable and sortable JSON columns', function (): void {
    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($this->findings, 'json-column-searchable'));

    expect($subjects)->toBe([ProductResource::class . '::meta', ProductResource::class . '::meta:sort']);
});

it('flags resources without a policy or without viewAny', function (): void {
    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($this->findings, 'resource-without-policy'));

    expect($subjects)->toBe([ProductResource::class, NoteResource::class . ':viewAny'])
        ->and(findingsFor($this->findings, 'resource-without-policy')[0]->severity)->toBe(Severity::Warning);
});

it('does not flag tenant scoping on a panel without tenancy', function (): void {
    expect(findingsFor($this->findings, 'tenant-filter-missing'))->toBeEmpty();
});

it('flags a resource whose model lacks the tenant relationship on a tenant panel', function (): void {
    $findings = app(Doctor::class)->run('tenant');

    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($findings, 'tenant-filter-missing'));

    expect($subjects)->toBe([ProductResource::class]);
});

it('flags translation keys missing for a locale but not existing framework keys', function (): void {
    $found = findingsFor($this->findings, 'missing-translation-key');

    expect($found)->toHaveCount(1)
        ->and($found[0]->subject)->toBe(PostResource::class . '::doctor-fixture.missing_label@en')
        ->and($found[0]->line)->toBeInt();
});

it('flags permission names that the permission source does not know', function (): void {
    $found = findingsFor($this->findings, 'permission-name-drift');

    expect($found)->toHaveCount(1)
        ->and($found[0]->subject)->toEndWith('PostPolicy.php::publish_post')
        ->and($found[0]->file)->toEndWith('PostPolicy.php')
        ->and($found[0]->line)->toBe(19);
});

it('flags relationship selects that are not searchable', function (): void {
    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($this->findings, 'unbounded-relationship-select'));

    expect($subjects)->toBe([ProductResource::class . '::category_id']);
});

it('flags navigation badges that query without a cache', function (): void {
    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($this->findings, 'uncached-navigation-badge'));

    expect($subjects)->toBe([PostResource::class]);
});

it('flags Filament::getTenant() inside jobs', function (): void {
    $found = findingsFor($this->findings, 'tenant-in-job');

    expect($found)->toHaveCount(1)
        ->and($found[0]->file)->toEndWith('SyncCatalogJob.php')
        ->and($found[0]->line)->toBe(15)
        ->and($found[0]->subject)->toEndWith('Fixtures/Jobs/SyncCatalogJob.php:15');
});

it('flags importer columns that are not model attributes', function (): void {
    $subjects = array_map(fn (Finding $f): string => $f->subject, findingsFor($this->findings, 'import-transient-column'));

    expect($subjects)->toBe([ProductImporter::class . '::category_name']);
});

it('reports nothing for a clean resource', function (): void {
    $about = array_filter($this->findings, fn (Finding $f): bool => str_contains($f->subject, TagResource::class));

    expect($about)->toBeEmpty();
});

it('respects severities from config, including off', function (): void {
    config()->set('filament-doctor.rules.json-column-searchable', 'off');
    config()->set('filament-doctor.rules.uncached-navigation-badge', 'error');

    $findings = app(Doctor::class)->run('admin');

    expect(findingsFor($findings, 'json-column-searchable'))->toBeEmpty()
        ->and(findingsFor($findings, 'uncached-navigation-badge')[0]->severity)->toBe(Severity::Error);
});

it('skips ignored classes', function (): void {
    config()->set('filament-doctor.ignore', [ProductResource::class]);

    $findings = app(Doctor::class)->run('admin');

    expect(findingsFor($findings, 'json-column-searchable'))->toBeEmpty()
        ->and(findingsFor($findings, 'unbounded-relationship-select'))->toBeEmpty();
});

it('skips database-backed checks with the database disabled', function (): void {
    $findings = app(Doctor::class)->run('admin', useDatabase: false);

    expect(findingsFor($findings, 'import-transient-column'))->toBeEmpty()
        // JSON detection still works through the model cast.
        ->and(findingsFor($findings, 'json-column-searchable'))->not->toBeEmpty();
});
