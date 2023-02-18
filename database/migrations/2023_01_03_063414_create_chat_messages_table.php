<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->longText('message');
            $table->integer('type')->default(MESSAGE_CHAT);
            $table->bigInteger('chat_id')->unsigned()->nullable();
            $table->bigInteger('sender_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
