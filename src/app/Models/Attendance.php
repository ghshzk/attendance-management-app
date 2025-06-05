<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'attendance_status_id',
        'remark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    public function getBreakMinutesAttribute()
    {
        return $this->breakTimes->reduce(function ($carry, $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                return $carry + Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
            return $carry;
        }, 0);
    }

    public function getWorkMinutesAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $workTime = Carbon::parse($this->clock_in)
                ->diffInMinutes(Carbon::parse($this->clock_out));
            return $workTime - $this->break_minutes;
        }
        return null;
    }

    public function getWorkTimeAttribute()
    {
        $minutes = $this->work_minutes;
        if ($minutes === null) {
            return null;
        }
        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }

    public function getBreakTimeAttribute()
    {
        $minutes = $this->break_minutes;
        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }
}
