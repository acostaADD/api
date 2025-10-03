<?php

namespace App\Http\Controllers;

use App\Models\Empleados;
use Illuminate\Http\Request;
use App\Services\FirebaseService; // 👈 Importamos el servicio Firebase

class EmpleadosController extends Controller
{
    protected $firebase;

    // 🔥 Inyectamos el servicio de Firebase
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    // =====================================
    // 📁 MÉTODOS NORMALES (MySQL)
    // =====================================
    public function search(Request $request)
    {
        $query = Empleados::query();

        if ($dni = $request->input('dni')) {
            $query->where('dni', 'like', "%$dni%");
        }
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%$name%");
        }
        if ($apellido = $request->input('apellido')) {
            $query->where('apellido', 'like', "%$apellido%");
        }
        if ($cargo = $request->input('cargo')) {
            $query->where('cargo', 'like', "%$cargo%");
        }
        if (!is_null($en_planilla = $request->input('en_planilla'))) {
            $query->where('en_planilla', (bool)$en_planilla);
        }
        if ($descanso_fijo = $request->input('descanso_fijo')) {
            $query->where('descanso_fijo', 'like', "%$descanso_fijo%");
        }
        if ($fotografia = $request->input('fotografia')) {
            $query->where('fotografia', 'like', "%$fotografia%");
        }
        if ($empresa_id = $request->input('empresa_id')) {
            $query->where('empresa_id', $empresa_id);
        }

        $paginate = $request->input('paginate') ?? 10;
        $results = $query->paginate($paginate);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (!isset($data['empresa_id'])) {
            return response()->json(['message' => 'El campo empresa_id es obligatorio'], 422);
        }

        if (isset($data['en_planilla'])) {
            $data['en_planilla'] = (bool)$data['en_planilla'];
        }

        $empleados = Empleados::create($data);

        // 🔥 También guardamos en Firebase
        $this->firebase->addDocument('empleados', [
            'id' => $empleados->id,
            'dni' => $empleados->dni,
            'name' => $empleados->name,
            'apellido' => $empleados->apellido,
            'cargo' => $empleados->cargo,
            'empresa_id' => $empleados->empresa_id,
        ]);

        return response()->json($empleados, 201);
    }

    public function update(Request $request, $id)
    {
        $empleados = Empleados::find($id);
        if (!$empleados) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $data = $request->all();

        if (isset($data['en_planilla'])) {
            $data['en_planilla'] = (bool)$data['en_planilla'];
        }

        if (isset($data['empresa_id']) && !$data['empresa_id']) {
            return response()->json(['message' => 'empresa_id no puede ser null'], 422);
        }

        $empleados->update($data);

        // 🔥 También actualizamos en Firebase
        $this->firebase->updateDocument('empleados', (string) $empleados->id, $data);

        return response()->json($empleados, 200);
    }

    public function destroy($id)
    {
        $empleados = Empleados::find($id);
        if (!$empleados) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $empleados->delete();

        // 🔥 También eliminamos de Firebase
        $this->firebase->deleteDocument('empleados', (string) $id);

        return response()->json(['message' => 'Empleado eliminado'], 200);
    }

    // =====================================
    // 🔥 MÉTODOS EXCLUSIVOS PARA FIREBASE
    // =====================================

    public function getEmpleadosFirebase()
    {
        $empleados = $this->firebase->getCollection('empleados');
        return response()->json($empleados);
    }

    public function getEmpleadoByDniFirebase($dni)
    {
        $empleados = $this->firebase->getCollection('empleados');
        $empleado = collect($empleados)->firstWhere('dni', $dni);

        if (!$empleado) {
            return response()->json(['error' => 'Empleado no encontrado en Firebase'], 404);
        }

        return response()->json($empleado);
    }
}
