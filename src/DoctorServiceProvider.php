<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor;

use Nsumbadze\Doctor\Commands\DoctorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DoctorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-doctor';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasCommand(DoctorCommand::class);
    }
}
