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
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('place');
            $table->string('couverture')->nullable();
            $table->string( 'type');
            $table->Date('DateDebut');
            $table->Date('DateFin');
            $table->string('description');
            $table->string('couleur');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor( \App\Models\StructureDispositif::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
