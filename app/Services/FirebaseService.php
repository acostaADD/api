<?php

namespace App\Services;

use Kreait\Firebase\Contract\Firestore;

class FirebaseService
{
    protected $firestore;

    public function __construct(Firestore $firestore)
    {
        $this->firestore = $firestore->database();
    }

    /**
     * Obtener todos los empleados
     */
    public function getEmpleados()
    {
        try {
            $collection = $this->firestore->collection('empleados');
            $documents = $collection->documents();
            
            $empleados = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $empleados[] = [
                        'id' => $document->id(),
                        'data' => $document->data()
                    ];
                }
            }
            
            return $empleados;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener empleados: ' . $e->getMessage());
        }
    }

    /**
     * Obtener empleado por ID
     */
    public function getEmpleado($id)
    {
        try {
            $docRef = $this->firestore->collection('empleados')->document($id);
            $snapshot = $docRef->snapshot();
            
            if ($snapshot->exists()) {
                return [
                    'id' => $snapshot->id(),
                    'data' => $snapshot->data()
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener empleado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar empleado por DNI
     */
    public function getEmpleadoByDni($dni)
    {
        try {
            $collection = $this->firestore->collection('empleados');
            $query = $collection->where('dni', '=', $dni);
            $documents = $query->documents();
            
            foreach ($documents as $document) {
                if ($document->exists()) {
                    return [
                        'id' => $document->id(),
                        'data' => $document->data()
                    ];
                }
            }
            
            return null;
        } catch (\Exception $e) {
            throw new \Exception('Error al buscar empleado por DNI: ' . $e->getMessage());
        }
    }

    /**
     * Crear nuevo empleado
     */
    public function createEmpleado($data)
    {
        try {
            $collection = $this->firestore->collection('empleados');
            $newDoc = $collection->add($data);
            
            return [
                'id' => $newDoc->id(),
                'data' => $data
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al crear empleado: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar empleado
     */
    public function updateEmpleado($id, $data)
    {
        try {
            $docRef = $this->firestore->collection('empleados')->document($id);
            $docRef->set($data, ['merge' => true]);
            
            return [
                'id' => $id,
                'data' => $data
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar empleado: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar empleado
     */
    public function deleteEmpleado($id)
    {
        try {
            $docRef = $this->firestore->collection('empleados')->document($id);
            $docRef->delete();
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar empleado: ' . $e->getMessage());
        }
    }

    /**
     * Registrar asistencia
     */
    public function registrarAsistencia($dni, $distancia = null)
    {
        try {
            // Buscar empleado
            $empleado = $this->getEmpleadoByDni($dni);
            
            if (!$empleado) {
                throw new \Exception('Empleado no encontrado con DNI: ' . $dni);
            }
            
            // Crear registro de asistencia
            $asistenciaData = [
                'empleado_id' => $empleado['id'],
                'dni' => $dni,
                'nombre' => $empleado['data']['apellido'] ?? 'Sin nombre',
                'cargo' => $empleado['data']['cargo'] ?? 'Sin cargo',
                'fecha' => now()->toIso8601String(),
                'timestamp' => now()->timestamp,
                'distancia' => $distancia,
                'dispositivo' => 'Sistema Laravel'
            ];
            
            $collection = $this->firestore->collection('asistencias');
            $newDoc = $collection->add($asistenciaData);
            
            return [
                'id' => $newDoc->id(),
                'data' => $asistenciaData
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al registrar asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Obtener asistencias
     */
    public function getAsistencias($limit = 50)
    {
        try {
            $collection = $this->firestore->collection('asistencias');
            $query = $collection->orderBy('timestamp', 'DESC')->limit($limit);
            $documents = $query->documents();
            
            $asistencias = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $asistencias[] = [
                        'id' => $document->id(),
                        'data' => $document->data()
                    ];
                }
            }
            
            return $asistencias;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener asistencias: ' . $e->getMessage());
        }
    }
}