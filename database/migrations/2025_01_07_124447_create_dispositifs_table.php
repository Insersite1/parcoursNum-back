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
        Schema::create('dispositifs', function (Blueprint $table) {
            $table->id();
            $table->String('name');
            $table->String('couverture')->nullable();
            $table->Date('DateDebut');
            $table->Date('DateFin');
            $table->enum('statut',['Active', 'Inactive'])->default('active');
            $table->String('pays');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositifs');
    }
};
