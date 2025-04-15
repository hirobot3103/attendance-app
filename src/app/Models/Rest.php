<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_in',
        'rest_out',
    ];

    public function attendances()
    {
        return $this->belongsTo('App\Models\Attendance')->orderBy('rest_in', 'asc');
    }
}
