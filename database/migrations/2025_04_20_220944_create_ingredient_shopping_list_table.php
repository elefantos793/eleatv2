<?php

use App\Models\Ingredient;
use App\Models\ShoppingList;
use App\Models\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredient_shopping_list', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ShoppingList::class)->constrained('shopping_lists');
            $table->foreignIdFor(Ingredient::class)->constrained('ingredients');
            $table->foreignIdFor(Unit::class)->constrained('units');
            $table->decimal('amount');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_shopping_list');
    }
};
