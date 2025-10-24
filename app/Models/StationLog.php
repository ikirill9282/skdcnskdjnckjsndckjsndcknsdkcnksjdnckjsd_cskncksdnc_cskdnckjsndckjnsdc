<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'event_type',
        'washing_machine_number',
        'program_number',
        'white_loading',
        'signal_1',
        'signal_2',
        'machine_signals',
        'detergent_signals',
        'comment',
    ];

    protected $casts = [
        'machine_signals' => 'array',
        'detergent_signals' => 'array',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
