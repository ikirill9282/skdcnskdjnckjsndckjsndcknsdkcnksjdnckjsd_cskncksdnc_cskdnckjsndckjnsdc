<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationSettingBlockUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'block_number',
        'changed_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
