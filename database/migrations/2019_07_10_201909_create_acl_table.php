<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mqtt_acl', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('allow')->default(1);
            $table->char('ipaddr',60)->nullable();
            $table->char('username',100)->nullable();
            $table->char('clientid',100)->nullable();            
            $table->integer('access');
            $table->char('topic',255)->nullable();
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
        Schema::dropIfExists('mqtt_acl');
    }
}
