<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * getNavigationBadge() on a Resource that queries without any cache call.
 *
 * @implements Rule<ClassMethod>
 */
final class UncachedNavigationBadgeRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name->toString() !== 'getNavigationBadge') {
            return [];
        }

        $class = $scope->getClassReflection();

        if ($class === null || ! $class->isSubclassOf('Filament\Resources\Resource')) {
            return [];
        }

        $finder = new NodeFinder;
        $body = $node->stmts ?? [];

        $queries = $finder->find($body, function (Node $node): bool {
            if ($node instanceof StaticCall || $node instanceof MethodCall) {
                $name = $node->name instanceof Node\Identifier ? $node->name->toString() : null;

                return in_array($name, ['count', 'query', 'where', 'whereIn', 'all', 'exists', 'sum'], true);
            }

            return false;
        });

        if ($queries === []) {
            return [];
        }

        $cached = $finder->find($body, function (Node $node): bool {
            if ($node instanceof StaticCall && $node->class instanceof Name) {
                return in_array($node->class->getLast(), ['Cache'], true);
            }

            if ($node instanceof FuncCall && $node->name instanceof Name) {
                return $node->name->toString() === 'cache';
            }

            if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
                return in_array($node->name->toString(), ['remember', 'rememberForever', 'flexible'], true);
            }

            return false;
        });

        if ($cached !== []) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s::getNavigationBadge() queries the database on every render; wrap it in Cache::remember() or Cache::flexible().',
                $class->getDisplayName(),
            ))
                ->identifier('filamentDoctor.uncachedNavigationBadge')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
