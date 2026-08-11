<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'person_name',
        'shift_start',
        'shift_end',
        'days',
        'date',
    ];
}
