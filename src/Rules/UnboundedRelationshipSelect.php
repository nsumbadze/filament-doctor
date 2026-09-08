<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Forms\Components\Select;
use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;
use Throwable;

/**
 * A relationship Select that is not searchable loads every related row into
 * the page on each render. Fine for ten countries, fatal for fifty thousand
 * customers.
 */
class UnboundedRelationshipSelect extends AbstractRule
{
    public function id(): string
    {
        return 'unbounded-relationship-select';
    }

    public function description(): string
    {
        return 'Relationship selects that load every option because they are not searchable';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            $form = $inspector->form($resource);

            if ($form === null) {
                continue;
            }

            try {
                $components = $form->getFlatComponents(withHidden: true);
            } catch (Throwable) {
                continue;
            }

            foreach ($components as $component) {
                if (! $component instanceof Select) {
                    continue;
                }

                try {
                    $relationship = $component->getRelationshipName();
                    $searchable = $component->isSearchable();
                } catch (Throwable) {
                    continue;
                }

                if ($relationship === null || $searchable) {
                    continue;
                }

                $name = $component->getName();

                yield $this->finding("{$resource}::{$name}", "Select \"{$name}\" loads every \"{$relationship}\" row; add ->searchable() (and ->preload() if the list is small).", $resource, $inspector->methodLine($resource, 'form'));
            }
        }
    }
}
