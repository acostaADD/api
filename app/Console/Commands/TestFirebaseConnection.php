<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class TestFirebaseConnection extends Command
{
    protected $signature = 'firebase:test';
    protected $description = 'Probar conexión con Firebase Firestore';

    public function handle()
    {
        try {
            // Obtener la instancia del servicio Firebase (Firestore)
            $firebase = app(FirebaseService::class);
            
            $this->info('✅ Conexión con Firebase Firestore exitosa.');
            
            // Probar obteniendo empleados
            $empleados = $firebase->getEmpleados();
            $this->info('📋 Total de empleados: ' . count($empleados));
            
            // Probar creando un registro de prueba
            $testData = [
                'dni' => '99999999',
                'apellido' => 'Prueba Test',
                'cargo' => 'Testing',
                'created_at' => now()->toIso8601String()
            ];
            
            $result = $firebase->createEmpleado($testData);
            $this->info('✅ Empleado de prueba creado con ID: ' . $result['id']);
            
            // Eliminar el registro de prueba
            $firebase->deleteEmpleado($result['id']);
            $this->info('🗑️  Empleado de prueba eliminado.');
            
            $this->info('✅ Todas las pruebas pasaron correctamente.');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}