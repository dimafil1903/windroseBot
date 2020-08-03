<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViberUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viber_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("user_id")->unique();
            $table->string("name")->nullable();
            $table->string("avatar")->nullable();
            $table->string("country");
            $table->string("language");
            $table->string("lang")->nullable();
            $table->string("primary_device_os")->nullable();
            $table->string("api_version")->nullable();
            $table->string("viber_version")->nullable();
            $table->string("mcc")->nullable();
            $table->string("mnc")->nullable();
            $table->string("mdevice_typecc")->nullable();
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
        Schema::dropIfExists('viber_users');
    }
}
