<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Reporters;

use Illuminate\Console\OutputStyle;
use Nsumbadze\Doctor\Finding;

interface Reporter
{
    /**
     * @param  array<int, Finding>  $findings
     */
    public function report(array $findings, OutputStyle $output): void;
}
