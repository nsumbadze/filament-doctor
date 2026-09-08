<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * getNavigationBadge() runs on every page render for every user. Without a
 * cache that is a COUNT(*) per resource per request.
 */
class UncachedNavigationBadge extends AbstractRule
{
    public function id(): string
    {
        return 'uncached-navigation-badge';
    }

    public function description(): string
    {
        return 'Navigation badges computed on every render without caching';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            $source = $inspector->methodSource($resource, 'getNavigationBadge');

            if ($source === null) {
                continue;
            }

            if (! preg_match('/(::count\(|->count\(|::query\(|::where|->where|::all\(|DB::)/', $source)) {
                continue;
            }

            if (preg_match('/(Cache::|\bcache\(|->remember|->flexible\(|Cached)/', $source)) {
                continue;
            }

            yield $this->finding($resource, 'getNavigationBadge() queries the database on every render; wrap it in Cache::remember() or Cache::flexible().', $resource, $inspector->methodLine($resource, 'getNavigationBadge'));
        }
    }
}
