<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'program_number',
        'name',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
