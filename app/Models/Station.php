<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

		protected $fillable = [
				'code',
				'name',
				'region',
				'company_id',
				'is_active',
				'current_status',
				'current_detergent',
				'current_volume',
				'current_washing_machine',
				'current_process_completion',
				'machines_data',
				'detergents_data',
				'auto_programs_data',
				'manual_programs_data',
				'activation_date',
				'days_worked',
				'service_date',
				'warnings',
				'errors',
				'program_names'
		];

		protected $casts = [
				'is_active' => 'boolean',
				'activation_date' => 'date',
				'service_date' => 'date',
				'machines_data' => 'array',
				'detergents_data' => 'array',
				'auto_programs_data' => 'array',
				'manual_programs_data' => 'array',
				'program_names' => 'array',
		];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
		public function logs()
		{
				return $this->hasMany(StationLog::class);
		}
		public function programs()
		{
				return $this->hasMany(Program::class);
		}

}
