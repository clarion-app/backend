<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::create('local_nodes', function (Blueprint $table) {
            $table->id();
            $table->uuid('node_id');
            $table->string('name');
            $table->string('backend_url');
            $table->string('wallet_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('local_nodes');
    }
};