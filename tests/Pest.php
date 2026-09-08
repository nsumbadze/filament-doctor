<?php

declare(strict_types=1);

use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * @param  array<int, Finding>  $findings
 * @return array<int, Finding>
 */
function findingsFor(array $findings, string $rule, ?string $subjectContains = null): array
{
    return array_values(array_filter(
        $findings,
        fn (Finding $finding): bool => $finding->rule === $rule
            && ($subjectContains === null || str_contains($finding->subject, $subjectContains)),
    ));
}
