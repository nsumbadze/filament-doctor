<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\PHPStan;

use Nsumbadze\Doctor\PHPStan\Rules\TenantInJobRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TenantInJobRule>
 */
final class TenantInJobRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TenantInJobRule;
    }

    public function test_reports_tenant_access_in_queued_classes(): void
    {
        $this->analyse([__DIR__ . '/data/tenant-in-job.php'], [
            ['Filament::getTenant() is null inside TenantInJobData\QueuedJob (queued jobs, importers and exporters run outside the request). Pass the tenant explicitly.', 15],
            ['Filament::getTenantId() is null inside TenantInJobData\ProductImporter (queued jobs, importers and exporters run outside the request). Pass the tenant explicitly.', 23],
        ]);
    }
}
