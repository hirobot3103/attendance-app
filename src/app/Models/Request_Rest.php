<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_Rest extends Model
{
    use HasFactory;

    protected $table = 'request_rests';

    protected $fillable = [
        'attendance_id',
        'rest_id',
        'rest_in',
        'rest_out',
    ];
}
