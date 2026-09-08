<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = ['meta' => 'array'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
