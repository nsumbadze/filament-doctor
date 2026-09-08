<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * Filament::getTenant() is resolved from the HTTP request. Inside queued jobs,
 * importers and exporters it is always null.
 */
class TenantInJob extends AbstractRule
{
    public function id(): string
    {
        return 'tenant-in-job';
    }

    public function description(): string
    {
        return 'Filament::getTenant() used inside jobs, importers or exporters';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->phpFilesIn((array) config('filament-doctor.paths.jobs', [])) as $file) {
            $lines = file($file);

            if ($lines === false) {
                continue;
            }

            $subjectFile = $this->relativePath($file);

            foreach ($lines as $index => $line) {
                if ($this->isCommentLine($line)) {
                    continue;
                }

                if (! preg_match('/(Filament::getTenant\(|filament\(\)->getTenant\(|Filament::getTenantId\()/', $line)) {
                    continue;
                }

                yield $this->finding($subjectFile . ':' . ($index + 1), 'Filament::getTenant() is null inside queued jobs, importers and exporters; pass the tenant id explicitly (e.g. through action options or the job constructor).', null, $index + 1)
                    ->withFile($file);
            }
        }
    }
}
