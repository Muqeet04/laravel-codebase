<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'person_name',
        'event_type',
        'timestamp',
        'date',
        'confidence',
        'late_minutes',
        'snapshot_path',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'date' => 'date',
        'confidence' => 'float',
        'late_minutes' => 'integer',
    ];
}
