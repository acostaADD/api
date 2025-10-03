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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique(); 
            $table->string('name');
            $table->string('apellido')->nullable();
            $table->string('cargo');
            $table->boolean('en_planilla')->nullable(); 
            $table->string('descanso_fijo');
            $table->string('fotografia');
            $table->unsignedBigInteger('empresa_id'); 
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict'); // no permite eliminar empresa si tiene empleados
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};

