<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * On a tenant panel Filament scopes a resource through the ownership
 * relationship. When the model has no such relationship and the resource does
 * not override getEloquentQuery(), every tenant sees every record.
 */
class TenantFilterMissing extends AbstractRule
{
    public function id(): string
    {
        return 'tenant-filter-missing';
    }

    public function description(): string
    {
        return 'Resources on a tenant panel that are not scoped to the tenant';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        if (! $panel->hasTenancy()) {
            return;
        }

        foreach ($inspector->resources() as $resource) {
            if (! $resource::isScopedToTenant()) {
                if (! $inspector->overrides($resource, 'getEloquentQuery')) {
                    yield $this->finding($resource, 'Resource opted out of tenant scoping and does not override getEloquentQuery(); every tenant sees every record.', $resource);
                }

                continue;
            }

            $relationship = $resource::getTenantOwnershipRelationshipName();
            $model = $inspector->model($resource);

            /** @var Model $instance */
            $instance = new $model;

            if (method_exists($instance, $relationship)) {
                continue;
            }

            yield $this->finding($resource, "Model {$model} has no \"{$relationship}\" relationship; Filament will throw when scoping this resource to the tenant. Add the relationship or set \$tenantOwnershipRelationshipName.", $resource);
        }
    }
}
