<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::create('join_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('node_id');
            $table->string('blockchain_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('join_requests');
    }
};