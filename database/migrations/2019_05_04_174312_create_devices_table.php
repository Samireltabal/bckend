<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('activated_at')->useCurrent();
            $table->string('device_id',25)->unique();
            $table->string('token',100)->unique();
            $table->integer('user_id');
            $table->string('type',25);
            $table->string('version',10);
            $table->string('channel',100)->unique();
            $table->ipAddress('internal_ip');            
            $table->ipAddress('external_ip');
            $table->macAddress('device_mac');
            $table->timestamps();
            $table->json('options');
            $table->boolean('active')->default(false);                        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
