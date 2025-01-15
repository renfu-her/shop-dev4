<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    use HasRecursiveRelationships;

    protected $fillable = [
        'parent_id',
        'name',
        'sort',
        'is_active',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')->orderBy('sort');
    }

    public function ancestors()
    {
        return $this->morphToAncestors();
    }

    public function descendants()
    {
        return $this->morphToDescendants()->orderBy('sort');
    }

    protected static function booted()
    {
        static::saving(function ($category) {
            if ($category->parent_id === $category->id) {
                $category->parent_id = null;
            }
        });
    }
}
