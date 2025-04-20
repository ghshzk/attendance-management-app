<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'break_time_id',
        'break_start',
        'break_end',
    ];

    public function correctionBreakTime()
    {
        return $this->belongsTo(CorrectionBreakTime::class);
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
}
