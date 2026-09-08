<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Policies;

use Nsumbadze\Doctor\Tests\Fixtures\Models\Note;
use Nsumbadze\Doctor\Tests\Fixtures\Models\User;

class NotePolicy
{
    public function update(User $user, Note $note): bool
    {
        return true;
    }
}
