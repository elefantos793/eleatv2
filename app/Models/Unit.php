<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function ingredientRecipes(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ingredient::class,
            IngredientRecipe::class,
            'unit_id',
            'id',
            'id',
            'ingredient_id'
        );
    }
}
