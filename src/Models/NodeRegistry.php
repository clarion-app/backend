<?php

namespace ClarionApp\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use ClarionApp\EloquentMultiChainBridge\EloquentMultiChainBridge;

class NodeRegistry extends Model
{
    use EloquentMultiChainBridge;

    protected $stream = 'NodeRegistry';
    protected $table = 'node_registry';
    protected $fillable = ['id', 'name'];
}