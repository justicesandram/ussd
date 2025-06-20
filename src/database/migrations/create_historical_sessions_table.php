<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historical_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_uid')->nullable()->index();
            $table->string('state');
            $table->longText('payload')->nullable();
            $table->string('locale')->nullable();
            $table->string('msisdn')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historical_sessions');
    }
};
