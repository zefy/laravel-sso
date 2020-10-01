<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrokerUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-sso.brokerUserTable', 'broker_user'), function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');
            $table->integer('broker_id')->foreign('broker_id')->references('id')->on('brokers');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-sso.brokerUserTable', 'broker_user'));
    }
}
