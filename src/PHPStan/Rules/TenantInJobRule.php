<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags Filament::getTenant() inside classes that run outside the request:
 * queued jobs, importers and exporters.
 *
 * @implements Rule<StaticCall>
 */
final class TenantInJobRule implements Rule
{
    private const QUEUED_PARENTS = [
        'Illuminate\Contracts\Queue\ShouldQueue',
        'Filament\Actions\Imports\Importer',
        'Filament\Actions\Exports\Exporter',
    ];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Identifier || ! in_array($node->name->toString(), ['getTenant', 'getTenantId'], true)) {
            return [];
        }

        if (! $node->class instanceof Name) {
            return [];
        }

        $class = $scope->resolveName($node->class);

        if ($class !== 'Filament\Facades\Filament') {
            return [];
        }

        $inClass = $scope->getClassReflection();

        if ($inClass === null) {
            return [];
        }

        foreach (self::QUEUED_PARENTS as $parent) {
            if ($inClass->is($parent) || $inClass->implementsInterface($parent)) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        'Filament::%s() is null inside %s (queued jobs, importers and exporters run outside the request). Pass the tenant explicitly.',
                        $node->name->toString(),
                        $inClass->getDisplayName(),
                    ))
                        ->identifier('filamentDoctor.tenantInJob')
                        ->build(),
                ];
            }
        }

        return [];
    }
}
