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
        'approved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
}
