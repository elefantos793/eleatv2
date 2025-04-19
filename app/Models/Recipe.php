<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'rating',
        'duration_in_minutes',
        'difficulty',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->using(IngredientRecipe::class)
            ->withPivot('amount', 'unit_id')
            ->withTimestamps();
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(IngredientRecipe::class);
    }
}
