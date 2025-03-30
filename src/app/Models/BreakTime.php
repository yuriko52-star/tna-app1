<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;
    
    protected $fillable = [
        
        'attendance_id',
        'clock_in',
        'clock_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function breakTimeEdits()
    {
        return $this->hasMany(BreakTimeEdit::class);
    }
}
