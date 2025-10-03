<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcacion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_marcacion'); 
            $table->dateTime('fecha_hora_ingreso'); 
            $table->dateTime('fecha_hora_salida');  
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcacion');
    }
};
