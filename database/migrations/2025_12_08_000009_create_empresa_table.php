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
        Schema::create('empresa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 150);
            $table->string('descripcion')->nullable();
            $table->string('logo', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('web')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefonos', 100)->nullable();
            $table->char('estatus', 1)->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
