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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('avatar')->nullable();
            $table->string('nom')->nullable();
            $table->string('Prenom')->nullable();
            $table->string('email')->unique();
            $table->string('numTelephone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('statut',['Active', 'Inactive'])->default('Active');
            $table->string('situation')->nullable();
            $table->enum('sexe',['M', 'F'])->nullable();
            $table->string('etatCivil')->nullable();
            $table->string('situationTravail')->nullable();
            $table->boolean('QP')->nullable();
            $table->boolean('ZRR')->nullable();
            $table->boolean('ETH')->nullable();
            $table->boolean('EPC')->nullable();
            $table->boolean('API')->nullable();
            $table->boolean('AE')->nullable();
            $table->string('Adresse')->nullable();
            $table->text('bibiographie')->nullable();
            $table->date('dateNaissance')->nullable();
            $table->string('codePostal')->nullable();
            $table->string('region')->nullable();
            $table->string('ville')->nullable();
            $table->string('NumSecuriteSocial')->nullable();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignIdFor( \App\Models\Structure::class)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
