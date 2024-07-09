<?php

namespace ClarionApp\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;

class AppPackage extends Model
{
    use EloquentMultiChainBridge;

    protected $fillable = ['organization', 'name', 'title', 'description', 'installed'];
}