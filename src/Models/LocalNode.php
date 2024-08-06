<?php

namespace ClarionApp\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class LocalNode extends Model
{
    protected $fillable = ['node_id', 'name', 'backend_url', 'wallet_address'];
}