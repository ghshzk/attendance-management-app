<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'break_minutes',
        'work_minutes',
        'remark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function correctionRequest()
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
