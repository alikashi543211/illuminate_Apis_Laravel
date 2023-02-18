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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('middle_name')->nullable();
            $table->string('full_name');
            $table->string('username')->nullable();
            $table->string('email', 100)->unique();
            $table->string('phone_no')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('photo')->nullable();
            $table->string('height')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('password')->nullable();
            $table->bigInteger('role_id')->unsigned();
            $table->integer('login_type')->unsigned()->default(LOGIN_EMAIL);
            $table->integer('status')->default(STATUS_ACTIVE);
            $table->text('about_you')->nullable();
            $table->text('social_token')->nullable();
            $table->string('social_user_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
