<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\LivewireServiceProvider;
use Nsumbadze\Doctor\DoctorServiceProvider;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Note;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Post;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Tag;
use Nsumbadze\Doctor\Tests\Fixtures\Models\User;
use Nsumbadze\Doctor\Tests\Fixtures\Panel\AdminPanelProvider;
use Nsumbadze\Doctor\Tests\Fixtures\Panel\TenantPanelProvider;
use Nsumbadze\Doctor\Tests\Fixtures\Policies\NotePolicy;
use Nsumbadze\Doctor\Tests\Fixtures\Policies\PostPolicy;
use Nsumbadze\Doctor\Tests\Fixtures\Policies\TagPolicy;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            // SupportServiceProvider must precede LivewireServiceProvider: it rebinds
            // Livewire's DataStore and Livewire turns that binding into a shared instance.
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            DoctorServiceProvider::class,
            AdminPanelProvider::class,
            TenantPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $fixtures = __DIR__ . '/Fixtures';

        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('filament-doctor.paths', [
            'jobs' => [$fixtures . '/Jobs'],
            'policies' => [$fixtures . '/Policies'],
            'importers' => [$fixtures . '/Imports'],
        ]);
        $app['config']->set('filament-doctor.locales', ['en']);
        $app['config']->set('filament-doctor.permissions', ['view_any_post']);
        $app['config']->set('filament-doctor.baseline', sys_get_temp_dir() . '/filament-doctor-baseline-' . getmypid() . '.json');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
    }
}
