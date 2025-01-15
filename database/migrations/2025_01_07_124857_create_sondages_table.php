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
        Schema::create('sondages', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable(); // Champ facultatif
            $table->timestamp('date_debut');
            $table->timestamp('date_fin');
            $table->boolean('est_publie')->default(false);
            $table->boolean('pour_tous_utilisateurs')->default(false);
            $table->enum('statut',['actif','inactif'])->default('actif');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sondages');
    }
};
