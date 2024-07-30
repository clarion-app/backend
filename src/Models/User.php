<?php
namespace ClarionApp\Backend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use ClarionApp\EloquentMultiChainBridge\EloquentMultiChainBridge;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use EloquentMultiChainBridge, HasApiTokens, HasRoles, SoftDeletes, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}