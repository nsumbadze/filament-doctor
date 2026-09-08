<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Reporters;

use Illuminate\Console\OutputStyle;
use Nsumbadze\Doctor\Severity;

/**
 * GitHub Actions workflow commands: one annotation per finding.
 */
final class GithubReporter implements Reporter
{
    public function report(array $findings, OutputStyle $output): void
    {
        foreach ($findings as $finding) {
            $level = $finding->severity === Severity::Error ? 'error' : 'warning';

            $properties = [];

            if ($finding->file !== null) {
                $properties[] = 'file=' . $this->relative($finding->file);
            }

            if ($finding->line !== null) {
                $properties[] = 'line=' . $finding->line;
            }

            $properties[] = 'title=' . $finding->rule;

            $output->writeln(sprintf(
                '::%s %s::%s',
                $level,
                implode(',', $properties),
                str_replace(["\r", "\n"], ['%0D', '%0A'], $finding->subject . ' — ' . $finding->message),
            ));
        }
    }

    private function relative(string $file): string
    {
        $base = rtrim(base_path(), '/') . '/';

        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }
}
