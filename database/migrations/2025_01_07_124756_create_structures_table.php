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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->String('couverture')->nullable();
            $table->String('nomcomplet');
            $table->date('dateExpire');
            $table->enum('statut',['Active', 'Inactive'])->default('active');
            $table->foreignIdFor( \App\Models\Dispositif::class);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
