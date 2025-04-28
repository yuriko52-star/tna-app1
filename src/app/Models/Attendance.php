<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendanceEdits()
    {
        return $this->hasMany(AttendanceEdit::class);
    }
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
    public function getHasPendingEditAttribute()
    {
        return $this->attendanceEdits()
            ->whereNull('approved_at')
            ->where('edited_by_admin',0)
            ->exists();
    }
}
