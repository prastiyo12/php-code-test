<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory; // ← tambahkan ini
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // ← tambahkan HasFactory di sini

    protected $fillable = ['email', 'password', 'name', 'role', 'active'];
    protected $hidden = ['password'];
    protected $casts = ['active' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
