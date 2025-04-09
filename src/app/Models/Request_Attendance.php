<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_Attendance extends Model
{
    use HasFactory;
    protected $table = 'request_attendances';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in',
        'clock_out',
        'status',
        'descript',
    ];
}
