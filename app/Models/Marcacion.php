<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marcacion extends Model
{
    use HasFactory;

    protected $table = 'marcacion';

    protected $fillable = [
        'tipo_marcacion',
        'fecha_hora_ingreso',
        'fecha_hora_salida',
        'empleado_id',
    ];

    
    protected $casts = [
        'fecha_hora_ingreso' => 'datetime',
        'fecha_hora_salida' => 'datetime',
    ];

    
    public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'empleado_id', 'id');
    }

    
}
