<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\PHPStan;

use Nsumbadze\Doctor\PHPStan\Rules\UncachedNavigationBadgeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UncachedNavigationBadgeRule>
 */
final class UncachedNavigationBadgeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UncachedNavigationBadgeRule;
    }

    public function test_reports_uncached_badges(): void
    {
        $this->analyse([__DIR__ . '/data/navigation-badge.php'], [
            ['NavigationBadgeData\UncachedResource::getNavigationBadge() queries the database on every render; wrap it in Cache::remember() or Cache::flexible().', 13],
        ]);
    }
}
