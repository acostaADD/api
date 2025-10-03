<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\FirebaseService; // 🔥 Línea agregada

class EmpresasController extends Controller
{
    protected $firebase; // 🔥 Línea agregada

    public function __construct(FirebaseService $firebase) // 🔥 Línea agregada
    {
        $this->firebase = $firebase;
    }

    public function search(Request $request)
    {
        $name = $request->input('name');
        $ruc = $request->input('ruc');
        $phone = $request->input('phone');   
        $sede = $request->input('sede');
        $logo = $request->input('logo');
        $direccion = $request->input('direccion');
        $ciudad = $request->input('ciudad');
        $actividad_comercial = $request->input('actividad_comercial'); 
        $paginate = $request->input('paginate') ?? 10; 

        $users = Empresas::query();

        if ($name) {
            $users->where('name', 'like', "%$name%");
        }
        if ($ruc) {
            $users->where('ruc', 'like', "%$ruc%");
        }
        if ($phone) {
            $users->where('phone', 'like', "%$phone%");
        }
        if ($sede) {
            $users->where('sede', 'like', "%$sede%");
        }
        if ($logo) {
            $users->where('logo', 'like', "%$logo%");
        }
        if ($direccion) {
            $users->where('direccion', 'like', "%$direccion%");
        }
        if ($ciudad) {
            $users->where('ciudad', 'like', "%$ciudad%");
        }
        if ($actividad_comercial) {
            $users->where('actividad_comercial', 'like', "%$actividad_comercial%");
        }

        $users = $users->paginate($paginate);
        return response()->json($users);
    }

    public function login(Request $request)
    {
        $user = Empresas::where('ruc', $request->ruc)->first();

        if (!$user) {
            return response()->json(['message' => 'Empresa no encontrada'], 401);
        }
        return response()->json([
            'message' => 'Credenciales Correctas',
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        $empresas = Empresas::create($request->all());

        // 🔥 Agregado: sincronizar con Firebase
        $this->firebase->save('empresas', (string)$empresas->id, $empresas->toArray());

        return response()->json($empresas, 201);
    }

    public function update(Request $request, $id)
    {
        $empresas = Empresas::find($id);
        if (!$empresas) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $empresas->update($request->all());

        // 🔥 Agregado: actualizar también en Firebase
        $this->firebase->update('empresas', (string)$empresas->id, $empresas->toArray());

        return response()->json($empresas, 200);
    }

    public function destroy($id)
    {
        $empresas = Empresas::find($id);
        if (!$empresas) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $empresas->delete();

        // 🔥 Agregado: eliminar también en Firebase
        $this->firebase->delete('empresas', (string)$id);

        return response()->json(['message' => 'Cliente eliminado'], 200);
    }
}
