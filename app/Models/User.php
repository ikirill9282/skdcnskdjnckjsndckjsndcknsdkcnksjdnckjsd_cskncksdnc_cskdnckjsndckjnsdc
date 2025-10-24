<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser

{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
		public function companies()
		{
				return $this->belongsToMany(Company::class)->withTimestamps();
		}

		public function stations()
		{
				return $this->belongsToMany(Station::class)->withTimestamps();
		}
		public function canAccessPanel(Panel $panel): bool
		{
				return $this->hasRole(['super-admin', 'admin']);
		}


}
