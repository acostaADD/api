<?php

namespace App\Http\Controllers;

use App\Models\Empleados;
use App\Models\Marcacion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\FirebaseService; // ✅ Agregado para usar Firebase

class MarcacionController extends Controller
{
    protected $firebase; // ✅ Propiedad para Firebase

    // ✅ Constructor: inyección del servicio Firebase
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function search(Request $request)
    {
        $query = Marcacion::query();

        if ($tipo = $request->input('tipo_marcacion')) {
            $query->where('tipo_marcacion', 'like', "%{$tipo}%");
        }
        if ($ingreso = $request->input('fecha_hora_ingreso')) {
            $query->whereDate('fecha_hora_ingreso', $ingreso);
        }
        if ($salida = $request->input('fecha_hora_salida')) {
            $query->whereDate('fecha_hora_salida', $salida);
        }
        if ($empleadoId = $request->input('empleado_id')) {
            $query->where('empleado_id', $empleadoId);
        }

        $paginate = $request->input('paginate') ?? 10;
        $results = $query->paginate($paginate);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $request->validate([
            'empleado_id'    => 'required|exists:empleados,id',
            'tipo_marcacion' => 'required|string'
        ]);

        $now = Carbon::now('America/Lima');

        if (strtolower($request->tipo_marcacion) === 'entrada') {
            $marcacion = new Marcacion();
            $marcacion->tipo_marcacion = 'Entrada';
            $marcacion->empleado_id = $request->empleado_id;
            $marcacion->fecha_hora_ingreso = $now;
            $marcacion->save();
        } elseif (strtolower($request->tipo_marcacion) === 'salida') {
            $marcacion = Marcacion::where('empleado_id', $request->empleado_id)
                            ->whereNotNull('fecha_hora_ingreso')
                            ->whereNull('fecha_hora_salida')
                            ->orderBy('fecha_hora_ingreso', 'desc')
                            ->first();

            if (!$marcacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay marcación de entrada pendiente para registrar la salida.'
                ], 400);
            }

            $marcacion->tipo_marcacion = 'Salida';
            $marcacion->fecha_hora_salida = $now;
            $marcacion->save();
        }

        // ✅ Enviar a Firebase sin modificar tu flujo actual
        $this->firebase->sendData('marcaciones/' . $marcacion->id, $marcacion->toArray());

        return response()->json($marcacion, 201);
    }

    public function update(Request $request, $id)
    {
        $marcacion = Marcacion::find($id);
        if (!$marcacion) {
            return response()->json(['message' => 'Marcación no encontrada'], 404);
        }

        $request->validate([
            'empleado_id'    => 'required|exists:empleados,id',
            'tipo_marcacion' => 'required|string'
        ]);

        $now = Carbon::now('America/Lima');

        if (strtolower($request->tipo_marcacion) === 'entrada') {
            $marcacion->tipo_marcacion = 'Entrada';
            $marcacion->fecha_hora_ingreso = $now;
        } elseif (strtolower($request->tipo_marcacion) === 'salida') {
            $marcacion->tipo_marcacion = 'Salida';
            $marcacion->fecha_hora_salida = $now;
        }

        $marcacion->empleado_id = $request->empleado_id;
        $marcacion->save();

        // ✅ Actualizar también en Firebase
        $this->firebase->sendData('marcaciones/' . $marcacion->id, $marcacion->toArray());

        return response()->json($marcacion, 200);
    }

    public function destroy($id)
    {
        $marcacion = Marcacion::find($id);
        if (!$marcacion) {
            return response()->json(['message' => 'Marcación no encontrada'], 404);
        }

        $marcacion->delete();

        // ✅ Eliminar también del Firebase Realtime Database
        $this->firebase->sendData('marcaciones/' . $id, null);

        return response()->json(['message' => 'Marcación eliminada'], 200);
    }

    public function getByDni($dni)
    {
        $dni = trim($dni);

        $empleado = Empleados::with(['marcacion' => function ($query) {
            $query->orderBy('fecha_hora_ingreso', 'desc')->limit(1);
        }])->where('dni', $dni)->first();

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'DNI no encontrado en los registros'
            ], 404);
        }

        $ultimaMarcacion = $empleado->marcacion->first() ?? null;

        return response()->json([
            'success' => true,
            'empleado' => [
                'id' => $empleado->id,
                'dni' => $empleado->dni,
                'name' => $empleado->name,
                'apellido' => $empleado->apellido,
                'cargo' => $empleado->cargo,
                'en_planilla' => $empleado->en_planilla,
                'descanso_fijo' => $empleado->descanso_fijo,
                'fotografia' => $empleado->fotografia,
                'empresa_id' => $empleado->empresa_id,
            ],
            'ultima_marcacion' => $ultimaMarcacion ? [
                'tipo_marcacion' => $ultimaMarcacion->tipo_marcacion,
                'fecha_hora_ingreso' => $ultimaMarcacion->fecha_hora_ingreso,
                'fecha_hora_salida' => $ultimaMarcacion->fecha_hora_salida,
            ] : null
        ], 200);
    }

    public function empleadosPorEmpresaConMarcacion($empresa_id)
    {
        $empleados = Empleados::where('empresa_id', $empresa_id)
            ->with(['marcacion' => function ($query) {
                $query->orderBy('fecha_hora_ingreso', 'desc')->limit(1);
            }])
            ->get();

        $data = $empleados->map(function ($empleado) {
            $ultimaMarcacion = $empleado->marcacion->first();

            return [
                'id' => $empleado->id,
                'dni' => $empleado->dni,
                'name' => $empleado->name,
                'apellido' => $empleado->apellido,
                'cargo' => $empleado->cargo,
                'en_planilla' => $empleado->en_planilla,
                'descanso_fijo' => $empleado->descanso_fijo,
                'fotografia' => $empleado->fotografia,
                'empresa_id' => $empleado->empresa_id,
                'created_at' => $empleado->created_at,
                'updated_at' => $empleado->updated_at,
                'ultima_marcacion' => $ultimaMarcacion ? [
                    'tipo_marcacion' => $ultimaMarcacion->tipo_marcacion,
                    'fecha_hora_ingreso' => $ultimaMarcacion->fecha_hora_ingreso,
                    'fecha_hora_salida' => $ultimaMarcacion->fecha_hora_salida,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'empresa_id' => $empresa_id,
            'empleados' => $data
        ]);
    }
}
