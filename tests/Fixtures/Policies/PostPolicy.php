<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Policies;

use Nsumbadze\Doctor\Tests\Fixtures\Models\Post;
use Nsumbadze\Doctor\Tests\Fixtures\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_post');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('publish_post');
    }
}
