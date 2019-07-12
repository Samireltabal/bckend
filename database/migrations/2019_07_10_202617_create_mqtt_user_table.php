<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMqttUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mqtt_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('username',100)->nullable();
            $table->char('password',100)->nullable();
            $table->char('salt',35)->nullable();
            $table->tinyInteger('is_superuser')->default(0);
            $table->dateTime('created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mqtt_user');
    }
}
