<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'mobile',
        'password',
        'first_name',
        'last_name',
        'type_id',
        'status',
        'image'
    ];
 

    public function adminType()
    {
        return $this->belongsTo(AdminType::class, 'type_id');
    }
}
