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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->unique();
            $table->string('nombre');
            $table->string('telefono');
            $table->string('direccion');
            $table->string('password');
            $table->integer('rol');
            $table->integer('estado');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('codigo_verificacion')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
