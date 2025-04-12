<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();


            $table->index('name');
        });

        foreach ([['name'=>'Hackfleisch (Rind)'], ['name'=>'Gehackte Tomaten (Dose)'], ['name'=>'Salz'],] as $unit) {
            DB::table('ingredients')->insert($unit);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
