<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'date',
        'machine_number',
        'program_1',
        'program_2',
        'program_3',
        'program_4',
        'program_5',
        'program_6',
        'program_7',
        'program_8',
        'program_9',
        'program_10',
        'program_11',
        'program_12',
        'program_13',
        'program_14',
        'program_15',
        'program_16',
        'program_17',
        'program_18',
        'program_19',
        'total',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
