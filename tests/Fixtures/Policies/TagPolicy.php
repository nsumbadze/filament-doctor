<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Policies;

use Nsumbadze\Doctor\Tests\Fixtures\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
}
