<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Commands;

use Illuminate\Console\Command;
use Nsumbadze\Doctor\Baseline;
use Nsumbadze\Doctor\Doctor;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Reporters\GithubReporter;
use Nsumbadze\Doctor\Reporters\JsonReporter;
use Nsumbadze\Doctor\Reporters\Reporter;
use Nsumbadze\Doctor\Reporters\TableReporter;
use Nsumbadze\Doctor\Severity;

class DoctorCommand extends Command
{
    protected $signature = 'filament:doctor
        {--panel= : Only inspect this panel id}
        {--format=table : table, json or github}
        {--strict : Fail on warnings as well as errors}
        {--no-db : Skip checks that need a database connection}
        {--generate-baseline : Write every current finding to the baseline file and exit}
        {--no-baseline : Report findings even if they are in the baseline}';

    protected $description = 'Audit Filament panels for common mistakes (policies, JSON search, tenancy, translations, imports)';

    public function handle(Doctor $doctor): int
    {
        $panel = $this->option('panel');
        $findings = $doctor->run(is_string($panel) && $panel !== '' ? $panel : null, ! $this->option('no-db'));

        $baseline = Baseline::fromConfig();

        if ($this->option('generate-baseline')) {
            $baseline->write($findings);

            $this->components->info(sprintf('Baseline written to %s with %d finding(s).', $baseline->path(), count($findings)));

            return self::SUCCESS;
        }

        if (! $this->option('no-baseline')) {
            $findings = $baseline->filter($findings);
        }

        $this->reporter()->report($findings, $this->output);

        $errors = array_filter($findings, fn (Finding $finding): bool => $finding->severity === Severity::Error);

        if ($errors !== []) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $findings !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function reporter(): Reporter
    {
        return match ($this->option('format')) {
            'json' => new JsonReporter,
            'github' => new GithubReporter,
            default => new TableReporter,
        };
    }
}
