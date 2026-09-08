<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $guarded = [];
}
