<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * Without a policy every user who can log in can do everything with the
 * resource; with a policy whose viewAny() is missing the resource silently
 * disappears from navigation.
 */
class ResourceWithoutPolicy extends AbstractRule
{
    public function id(): string
    {
        return 'resource-without-policy';
    }

    public function description(): string
    {
        return 'Resources whose model has no policy, or a policy without viewAny()';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            $model = $inspector->model($resource);
            $policy = Gate::getPolicyFor($model);

            if ($policy === null) {
                yield $this->finding($resource, "No policy registered for {$model}; every authenticated user may view, create, edit and delete these records.", $resource);

                continue;
            }

            if (! method_exists($policy, 'viewAny')) {
                yield $this->finding("{$resource}:viewAny", 'Policy ' . $policy::class . ' has no viewAny() method, so the resource is hidden from every user.', $policy::class);
            }
        }
    }
}
