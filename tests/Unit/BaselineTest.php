<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Nsumbadze\Doctor\Baseline;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;

it('hides findings whose fingerprint is in the baseline', function (): void {
    $path = sys_get_temp_dir() . '/doctor-baseline-unit-' . uniqid() . '.json';
    $baseline = new Baseline($path);

    $known = new Finding('rule-a', Severity::Error, 'App\Foo', 'old message');
    $new = new Finding('rule-a', Severity::Error, 'App\Bar', 'message');

    $baseline->write([$known]);

    $reworded = new Finding('rule-a', Severity::Error, 'App\Foo', 'new wording');

    expect($baseline->filter([$reworded, $new]))->toHaveCount(1)
        ->and($baseline->filter([$reworded, $new])[0]->subject)->toBe('App\Bar');

    File::delete($path);
});

it('exposes docs urls and array form', function (): void {
    $finding = new Finding('json-column-searchable', Severity::Warning, 'X', 'msg', '/tmp/x.php', 3);

    expect($finding->docsUrl())->toBe('https://github.com/nsumbadze/filament-doctor/blob/main/docs/rules/json-column-searchable.md')
        ->and($finding->toArray())->toMatchArray(['rule' => 'json-column-searchable', 'severity' => 'warning', 'line' => 3]);
});
