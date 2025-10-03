<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iotmonitor extends Model
{
    use HasFactory;

    
    protected $table = 'iotmonitor';


    protected $fillable = [
        'device_name',
        'location',
        'distance_cm',
        'buzzer_status',
        'status',
    ];


    protected $casts = [
        'buzzer_status' => 'boolean', 
        'distance_cm'   => 'float',
    ];
}
