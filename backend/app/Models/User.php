<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cat;

class User extends Model
{
    protected $fillable = [
        'email',
        'password',
        'remember_token',
        'role_id',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts= [
        'id' => 'integer',
        'role_id' => 'integer',
    ];
    public function cats(): HasMany
    {
        return $this->hasMany(Cat::class);
    }
}
