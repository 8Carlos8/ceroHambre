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
        Schema::create('alimento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_donante')->unique();
            $table->string('nombre');
            $table->binary('foto')->nullable();
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('cantidad');
            $table->date('fecha_vencimiento');
            $table->integer('estado');
            $table->timestamps();

            $table->foreign('id_donante')->references('id')->on('donante')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alimento');
    }
};
