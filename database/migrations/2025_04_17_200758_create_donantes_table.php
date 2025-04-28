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
        Schema::create('donante', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('tipo_asociacion');
            $table->binary('logo')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('id_usuario')->references('id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donante');
    }
};
