<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean']; }
    public function orders() { return $this->hasMany(LabOrder::class, 'assigned_to'); }
    public function canManage(): bool { return in_array($this->role, ['admin','manager'], true); }
    public function isFinance(): bool { return in_array($this->role, ['admin','manager','accountant'], true); }
}
