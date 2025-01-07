<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('structure_dispositifs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor( \App\Models\Structure::class);
            $table->foreignIdFor( \App\Models\Dispositif::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_dispositifs');
    }
};
