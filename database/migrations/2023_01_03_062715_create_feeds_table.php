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
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('flair');
            $table->longText('body')->nullable();
            $table->boolean('type')->default(FEED_PUBLIC); // 1 = public, 2 = private
            $table->string('location')->nullable();
            $table->string('time')->nullable();
            $table->string('date')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('feeds', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feeds');
    }
};
