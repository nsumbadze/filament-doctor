<?php

declare(strict_types=1);

namespace NavigationBadgeData;

use App\Models\Post;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Cache;

class UncachedResource extends Resource
{
    public static function getNavigationBadge(): ?string
    {
        return (string) Post::query()->count();
    }
}

class CachedResource extends Resource
{
    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('posts', 60, fn () => (string) Post::query()->count());
    }
}

class StaticResource extends Resource
{
    public static function getNavigationBadge(): ?string
    {
        return 'new';
    }
}
