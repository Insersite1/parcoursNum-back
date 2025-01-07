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
        Schema::create('sceances', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('par')->nullable();
            $table->string('session_code');
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->foreignIdFor( \App\Models\Session::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sceances');
    }
};
