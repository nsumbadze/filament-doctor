<?php

declare(strict_types=1);

/*
 * Stand-ins for spatie/laravel-translatable and lara-zeus/spatie-translatable,
 * which are not installed in the test suite. The rules only look at trait names.
 */

namespace Spatie\Translatable {
    if (! trait_exists(HasTranslations::class, false)) {
        trait HasTranslations {}
    }
}

namespace LaraZeus\SpatieTranslatable\Resources\Concerns {
    if (! trait_exists(Translatable::class, false)) {
        trait Translatable {}
    }
}

namespace LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns {
    if (! trait_exists(Translatable::class, false)) {
        trait Translatable {}
    }
}

namespace LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns {
    if (! trait_exists(Translatable::class, false)) {
        trait Translatable {}
    }
}
