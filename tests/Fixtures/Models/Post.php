<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    protected $guarded = [];

    /** @var array<int, string> */
    public array $translatable = ['title'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
