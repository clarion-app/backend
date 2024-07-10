<?php

namespace ClarionApp\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;

class ComposerPackage extends Model
{
    use EloquentMultiChainBridge;

    protected $fillable = ['organization', 'name', 'app_id', 'installed'];
}