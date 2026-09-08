<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Reporters;

use Illuminate\Console\OutputStyle;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;

final class JsonReporter implements Reporter
{
    public function report(array $findings, OutputStyle $output): void
    {
        $errors = count(array_filter($findings, fn (Finding $finding): bool => $finding->severity === Severity::Error));

        $output->writeln((string) json_encode([
            'summary' => [
                'total' => count($findings),
                'errors' => $errors,
                'warnings' => count($findings) - $errors,
            ],
            'findings' => array_map(fn (Finding $finding): array => $finding->toArray(), $findings),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
