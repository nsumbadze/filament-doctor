<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

afterEach(function (): void {
    File::delete((string) config('filament-doctor.baseline'));
});

it('fails with a table report when errors exist', function (): void {
    $this->artisan('filament:doctor', ['--panel' => 'admin'])
        ->expectsOutputToContain('json-column-searchable')
        ->expectsOutputToContain('EditPost')
        ->assertFailed();
});

it('prints json with a summary', function (): void {
    $exit = Artisan::call('filament:doctor', ['--panel' => 'admin', '--format' => 'json']);
    $decoded = json_decode(Artisan::output(), true);

    expect($exit)->toBe(1)
        ->and($decoded['summary']['errors'])->toBeGreaterThan(0)
        ->and(array_column($decoded['findings'], 'rule'))->toContain('tenant-in-job', 'json-column-searchable');
});

it('prints github annotations', function (): void {
    $this->artisan('filament:doctor', ['--panel' => 'admin', '--format' => 'github'])
        ->expectsOutputToContain('title=translatable-concern-missing')
        ->expectsOutputToContain('Fixtures/Jobs/SyncCatalogJob.php,line=14,title=tenant-in-job::')
        ->assertFailed();
});

it('generates a baseline and then reports nothing', function (): void {
    $baseline = (string) config('filament-doctor.baseline');

    $this->artisan('filament:doctor', ['--panel' => 'admin', '--generate-baseline' => true])
        ->assertSuccessful();

    expect(File::exists($baseline))->toBeTrue();

    $this->artisan('filament:doctor', ['--panel' => 'admin'])
        ->expectsOutputToContain('No problems found.')
        ->assertSuccessful();

    $this->artisan('filament:doctor', ['--panel' => 'admin', '--no-baseline' => true])
        ->assertFailed();
});

it('passes when only warnings remain unless strict', function (): void {
    foreach (['translatable-concern-missing', 'json-column-searchable', 'tenant-filter-missing', 'permission-name-drift', 'tenant-in-job', 'import-transient-column'] as $rule) {
        config()->set("filament-doctor.rules.{$rule}", 'off');
    }

    $this->artisan('filament:doctor', ['--panel' => 'admin'])->assertSuccessful();
    $this->artisan('filament:doctor', ['--panel' => 'admin', '--strict' => true])->assertFailed();
});
