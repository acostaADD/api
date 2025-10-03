<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleados extends Model
{
    use HasFactory;
    
    protected $table = 'empleados';

    protected $fillable = [
        'dni',
        'name',
        'apellido',
        'cargo',
        'en_planilla',
        'descanso_fijo',
        'fotografia',
        'empresa_id',
    ];

    
    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    
    public function marcacion()
    {
        return $this->hasMany(Marcacion::class, 'empleado_id', 'id');
    }
}
