<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Reporters;

use Illuminate\Console\OutputStyle;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;

final class TableReporter implements Reporter
{
    public function report(array $findings, OutputStyle $output): void
    {
        if ($findings === []) {
            $output->success('No problems found.');

            return;
        }

        $grouped = [];

        foreach ($findings as $finding) {
            $grouped[$finding->rule][] = $finding;
        }

        ksort($grouped);

        foreach ($grouped as $rule => $ruleFindings) {
            $severity = $ruleFindings[0]->severity;
            $tag = $severity === Severity::Error ? 'error' : 'comment';

            $output->writeln(sprintf(
                ' <%s>%s %s</%s> <fg=gray>(%d)</> <fg=gray>%s</>',
                $tag,
                $severity->symbol(),
                $rule,
                $tag,
                count($ruleFindings),
                $ruleFindings[0]->docsUrl(),
            ));

            foreach ($ruleFindings as $finding) {
                $location = $finding->file !== null
                    ? '<fg=gray>' . $this->relative($finding->file) . ($finding->line !== null ? ':' . $finding->line : '') . '</>'
                    : '';

                $output->writeln("   {$finding->subject}");
                $output->writeln("     {$finding->message}");

                if ($location !== '') {
                    $output->writeln("     {$location}");
                }
            }

            $output->newLine();
        }

        $errors = count(array_filter($findings, fn (Finding $finding): bool => $finding->severity === Severity::Error));
        $warnings = count($findings) - $errors;

        $output->writeln(sprintf(' <fg=gray>%d error(s), %d warning(s)</>', $errors, $warnings));
    }

    private function relative(string $file): string
    {
        $base = rtrim(base_path(), '/') . '/';

        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }
}
