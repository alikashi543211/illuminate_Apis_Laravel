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
        if (!Schema::hasColumn('users', 'facebook_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('facebook_url')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('users', 'twitter_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('twitter_url')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('users', 'instagram_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('instagram_url')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
