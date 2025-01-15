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
        //
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // Clé étrangère qui relie la question au sondage
            $table->foreignId('sondage_id')->constrained('sondages')->onDelete('cascade');
            $table->string('titre_question');
            $table->enum('type_question',['texte_court','choix_unique','choix_multiple'])->default('texte_court'); // choix_multiple, texte, etc.
            $table->json('options')->nullable(); // Options pour les questions à choix (ex: oui, non)
            $table->boolean('obligatoire')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
        //
    }
};
