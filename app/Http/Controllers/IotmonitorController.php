<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iotmonitor;
use App\Services\FirebaseService; // 🔹 Agregado para usar Firebase

class IotmonitorController extends Controller
{
    protected $firebase; // 🔹 Nueva propiedad

    // 🔹 Inyección del servicio Firebase
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index()
    {
        return response()->json(Iotmonitor::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:100',
            'location'    => 'nullable|string|max:150',
            'distance_cm' => 'required|numeric',
            'status'      => 'required|in:online,offline',
        ]);

        $validated['buzzer_status'] = ($validated['distance_cm'] > 0 && $validated['distance_cm'] < 20);

        $iot = Iotmonitor::create($validated);

        // 🔹 Enviar los datos a Firebase sin alterar la lógica actual
        $this->firebase->sendData('iot_data/' . $iot->id, $iot->toArray());

        return response()->json([
            'message' => 'Registro creado con éxito',
            'data'    => $iot
        ], 201);
    }

    public function show($id)
    {
        $iot = Iotmonitor::find($id);

        if (!$iot) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($iot, 200);
    }

    public function destroy($id)
    {
        $iot = Iotmonitor::find($id);

        if (!$iot) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $iot->delete();

        // 🔹 Eliminar también en Firebase (sin afectar la base local)
        $this->firebase->sendData('iot_data/' . $id, null);

        return response()->json(['message' => 'Registro eliminado con éxito'], 200);
    }
}
