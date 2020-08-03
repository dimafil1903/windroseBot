<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViberMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viber_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("user_id");
            $table->string("timestamp");
            $table->string("message_token");
            $table->string("chat_hostname");
            $table->string("type");
            $table->string("text")->nullable();
            $table->string("media")->nullable()->unsigned();
            $table->string("sticker_id")->nullable();
            $table->string("contact")->nullable();
            $table->string("file_name")->nullable();
            $table->string("thumbnail")->nullable();
            $table->string("location")->nullable();
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
        Schema::dropIfExists('viber_messages');
    }
}
