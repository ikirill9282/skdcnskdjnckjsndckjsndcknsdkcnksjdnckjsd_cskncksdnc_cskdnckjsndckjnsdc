<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationSettingValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'block_number',
        'setting_index',
        'value',
    ];

    protected $casts = [
        'block_number' => 'integer',
        'setting_index' => 'integer',
        'value' => 'string',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
