<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('iotmonitor', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');       
            $table->string('location')->nullable(); 
            $table->float('distance_cm');       
            $table->boolean('buzzer_status');   
            $table->enum('status', ['online', 'offline'])->default('online'); 
            $table->timestamps(); 
        });
    }

    public function down(): void {
        Schema::dropIfExists('iotmonitor');
    }
};
