<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Panel;

use Filament\Panel;
use Filament\PanelProvider;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\BrokenResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\NoteResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\ProductResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->login()
            ->resources([
                PostResource::class,
                ProductResource::class,
                TagResource::class,
                NoteResource::class,
                BrokenResource::class,
            ]);
    }
}
