<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViberConversationStartedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viber_conversation_started', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("user_id");
            $table->string("timestamp");
            $table->string("message_token");
            $table->string("chat_hostname");
            $table->string("type");
            $table->string("subscribed");
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
        Schema::dropIfExists('viber_conversation_started');
    }
}
