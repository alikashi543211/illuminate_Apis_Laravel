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
        Schema::create('feed_media', function (Blueprint $table) {
            $table->id();
            $table->string('media');
            $table->string('type'); // image, video
            $table->string('thumbnail')->nullable(); // image, video
            $table->bigInteger('feed_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('feed_media', function (Blueprint $table) {
            $table->foreign('feed_id')->references('id')->on('feeds')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_media');
    }
};
