<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
            $table->index('abbreviation');
        });

        foreach([
            ['name'=>'Gramm', 'abbreviation'=>'g'],
            ['name'=>'Kilogramm', 'abbreviation'=>'kg'],
            ['name'=>'Liter', 'abbreviation'=>'l'],
            ['name'=>'Milliliter', 'abbreviation'=>'ml'],
            ['name'=>'Stück', 'abbreviation'=>'Stk'],
            ['name'=>'Esslöffel', 'abbreviation'=>'EL'],
            ['name'=>'Teelöffel', 'abbreviation'=>'TL'],
        ] as $unit) {
            DB::table('units')->insert($unit);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
