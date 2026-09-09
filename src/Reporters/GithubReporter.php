<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Reporters;

use Illuminate\Console\OutputStyle;
use Nsumbadze\Doctor\Severity;
use Nsumbadze\Doctor\Support\Paths;

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
                $properties[] = 'file=' . $this->property(Paths::relative($finding->file));
            }

            if ($finding->line !== null) {
                $properties[] = 'line=' . $finding->line;
            }

            $properties[] = 'title=' . $this->property($finding->rule);

            $output->writeln(sprintf(
                '::%s %s::%s',
                $level,
                implode(',', $properties),
                $this->data($finding->subject . ' — ' . $finding->message),
            ));
        }
    }

    /**
     * Workflow commands reserve %, \r and \n in the message.
     */
    private function data(string $value): string
    {
        return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $value);
    }

    /**
     * Property values additionally reserve : and , as separators.
     */
    private function property(string $value): string
    {
        return str_replace([':', ','], ['%3A', '%2C'], $this->data($value));
    }
}
