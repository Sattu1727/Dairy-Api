<?php
// app/Models/AdminType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminType extends Model
{
    use HasFactory;

    protected $fillable = ['admin_type', 'permission'];

    public function users()
    {
        return $this->hasMany(AdminUser::class, 'type_id');
    }
}
