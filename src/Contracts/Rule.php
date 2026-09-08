<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Contracts;

use Filament\Panel;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Support\Inspector;

interface Rule
{
    /**
     * Kebab-case identifier, also the config key and the docs page name.
     */
    public function id(): string;

    public function description(): string;

    /**
     * @return iterable<int, Finding>
     */
    public function check(Panel $panel, Inspector $inspector): iterable;
}
