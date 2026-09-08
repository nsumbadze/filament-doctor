<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor;

use Filament\Facades\Filament;
use Filament\Panel;
use Nsumbadze\Doctor\Contracts\Rule;
use Nsumbadze\Doctor\Rules\ImportTransientColumn;
use Nsumbadze\Doctor\Rules\JsonColumnSearchable;
use Nsumbadze\Doctor\Rules\MissingTranslationKey;
use Nsumbadze\Doctor\Rules\PermissionNameDrift;
use Nsumbadze\Doctor\Rules\ResourceWithoutPolicy;
use Nsumbadze\Doctor\Rules\TenantFilterMissing;
use Nsumbadze\Doctor\Rules\TenantInJob;
use Nsumbadze\Doctor\Rules\TranslatableConcernMissing;
use Nsumbadze\Doctor\Rules\UnboundedRelationshipSelect;
use Nsumbadze\Doctor\Rules\UncachedNavigationBadge;
use Nsumbadze\Doctor\Support\Inspector;
use Throwable;

/**
 * Runs every enabled rule against every (or one) panel and returns findings
 * with their configured severity applied.
 */
class Doctor
{
    /** @var array<int, class-string<Rule>> */
    public const RULES = [
        TranslatableConcernMissing::class,
        JsonColumnSearchable::class,
        ResourceWithoutPolicy::class,
        TenantFilterMissing::class,
        MissingTranslationKey::class,
        PermissionNameDrift::class,
        UnboundedRelationshipSelect::class,
        UncachedNavigationBadge::class,
        TenantInJob::class,
        ImportTransientColumn::class,
    ];

    /**
     * @return array<int, Rule>
     */
    public function rules(): array
    {
        /** @var array<int, class-string<Rule>> $extra */
        $extra = (array) config('filament-doctor.extra_rules', []);

        return array_map(
            fn (string $rule): Rule => app($rule),
            [...self::RULES, ...$extra],
        );
    }

    /**
     * @return array<int, Panel>
     */
    public function panels(?string $panelId = null): array
    {
        if ($panelId !== null) {
            return [Filament::getPanel($panelId)];
        }

        return array_values(Filament::getPanels());
    }

    /**
     * @return array<int, Finding>
     */
    public function run(?string $panelId = null, bool $useDatabase = true): array
    {
        $findings = [];

        /** @var array<int, class-string> $ignored */
        $ignored = (array) config('filament-doctor.ignore', []);

        foreach ($this->panels($panelId) as $panel) {
            Filament::setCurrentPanel($panel);
            $panel->boot();

            $inspector = new Inspector($panel, $useDatabase, $ignored);

            foreach ($this->rules() as $rule) {
                $severity = $this->severityOf($rule);

                if ($severity === Severity::Off) {
                    continue;
                }

                try {
                    foreach ($rule->check($panel, $inspector) as $finding) {
                        $findings[] = $finding->withSeverity($severity);
                    }
                } catch (Throwable $exception) {
                    $findings[] = new Finding(
                        $rule->id(),
                        Severity::Warning,
                        $rule->id() . '@' . $panel->getId(),
                        "Rule could not run on panel \"{$panel->getId()}\": " . $exception->getMessage(),
                    );
                }
            }
        }

        return $this->unique($findings);
    }

    public function severityOf(Rule $rule): Severity
    {
        $configured = config("filament-doctor.rules.{$rule->id()}", 'warning');

        return Severity::tryFrom(is_string($configured) ? $configured : 'warning') ?? Severity::Warning;
    }

    /**
     * The same class can be registered in several panels; report it once.
     *
     * @param  array<int, Finding>  $findings
     * @return array<int, Finding>
     */
    protected function unique(array $findings): array
    {
        $seen = [];
        $unique = [];

        foreach ($findings as $finding) {
            $key = $finding->fingerprint();

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $finding;
        }

        return $unique;
    }
}
