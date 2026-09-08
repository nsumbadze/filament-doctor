<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor;

enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Off = 'off';

    public function symbol(): string
    {
        return match ($this) {
            self::Error => '✖',
            self::Warning => '▲',
            self::Off => '·',
        };
    }
}
