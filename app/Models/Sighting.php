<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sighting extends Model
{
    use HasFactory;

    protected $table = 'sightings';

    protected $fillable = [
        'person_name',
        'timestamp',
        'date',
        'snapshot_path',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'date' => 'date',
    ];
}
