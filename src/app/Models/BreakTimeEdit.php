<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeEdit extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'break_time_id',
        'user_id',
        'request_date',
        'target_date',
        'new_clock_in',
        'new_clock_out',
        'reason',
        'approved_at',
        'edited_by_admin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
    public function attendance()
    {
        return $this->hasOneThrough(
            Attendance::class,
            BreakTime::class,
            'id',
            'id',
            'break_time_id',
            'attendance_id'
        );
    }
}
