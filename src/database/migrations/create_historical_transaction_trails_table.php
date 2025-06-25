<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::create('historical_transaction_trails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_uid')->index();
            $table->string('message');
            $table->string('response');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historical_transaction_trails');
    }
};
