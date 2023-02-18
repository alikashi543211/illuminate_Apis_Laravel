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
        if (!Schema::hasColumn('users', 'latitude')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('latitude')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('users', 'longitude')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('longitude')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('users', 'profile_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('profile_status')->default(PROFILE_STATUS_PENDING)->after('id');
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
