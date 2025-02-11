<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Add this line

class AdminUser extends Authenticatable
{
    use HasFactory, HasApiTokens; // Add HasApiTokens here

    protected $fillable = [
        'username',
        'password',
        'first_name',
        'last_name',
        'type_id',
        'status',
    ];

    public function adminType()
    {
        return $this->belongsTo(AdminType::class, 'type_id');
    }
}
