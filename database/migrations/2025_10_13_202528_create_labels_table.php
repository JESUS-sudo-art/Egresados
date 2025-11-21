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
        Schema::create('labels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->integer('orden')->nullable();
            $table->boolean('status')->nullable()->default(true);
            $table->unsignedBigInteger('creado_por_id')->nullable()->index('labels_creado_por_id_fk');
            $table->unsignedBigInteger('actualizado_por_id')->nullable()->index('labels_actualizado_por_id_fk');
            $table->unsignedBigInteger('eliminado_por_id')->nullable()->index('labels_eliminado_por_id_fk');
            $table->timestamp('creado_en')->nullable()->useCurrent();
            $table->timestamp('actualizado_en')->useCurrentOnUpdate()->nullable();
            $table->timestamp('eliminado_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
