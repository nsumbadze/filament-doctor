<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Panel;

use Filament\Panel;
use Filament\PanelProvider;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Team;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\ProductResource;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('tenant')
            ->login()
            ->tenant(Team::class)
            ->resources([
                ProductResource::class,
                TagResource::class,
            ]);
    }
}
