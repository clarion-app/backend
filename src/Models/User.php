<?php
namespace ClarionApp\Backend;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;
use Laravel\Passport\HasApiTokens;

class User extends App\Models\User
{
    use EloquentMultiChainBridge, HasApiTokens, HasRoles, SoftDeletes;
}