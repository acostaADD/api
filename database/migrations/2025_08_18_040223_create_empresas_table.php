empresa migrate:<?php

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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string ('name');
            $table->string ('ruc')->unique();
            $table->string ('phone')->nullable();
            $table->string ('sede');
            $table->string ('logo');
            $table->string ('direccion');
            $table->string ('ciudad');
            $table->string ('actividad_comercial');
             
            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
