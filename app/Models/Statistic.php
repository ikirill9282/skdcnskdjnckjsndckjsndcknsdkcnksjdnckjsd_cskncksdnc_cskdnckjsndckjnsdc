<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'date',
        'data',
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'array',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
