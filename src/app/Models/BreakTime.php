<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BreakTime extends Model
{
    use HasFactory;
    use SoftDeletes;
    
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
    public function getHasPendingEditAttribute()
    {
        return $this->breakTimeEdits()
            ->whereNull('approved_at')
            ->where('edited_by_admin',0)
            ->exists();
    }
}
