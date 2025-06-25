<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('historical_session_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn')->index();
            $table
                ->foreignId('session_id')
                ->nullable()
                ->constrained('historical_sessions')
                ->cascadeOnDelete();
            $table->bigInteger('ussd_session');
            $table->string('last_screen');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historical_session_numbers');
    }
};
