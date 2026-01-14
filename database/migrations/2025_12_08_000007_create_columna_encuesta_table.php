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
        Schema::create('columna_encuesta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('encuesta_id')->index('columna_encuesta_encuesta_id_fk');
            $table->integer('valor')->nullable();
            $table->integer('orden')->default(0);
            $table->string('campo', 40)->nullable();
            $table->string('columna', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columna_encuesta');
    }
};
