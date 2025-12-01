<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nivel_estudio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->char('estatus', 1)->default('A');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nivel_estudio');
    }
};
