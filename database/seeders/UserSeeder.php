<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('users')->insert([
            [
                // Latitude and longitude => Devstudio
                'id' => 1,
                'latitude' => '31.46172104856044',
                'longitude' => '74.41434655159449',
                'full_name' => 'Super',
                'username' => 'admin',
                'email' => 'admin@devstudio.us',
                'password' => Hash::make('hash@321'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_ADMIN,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'latitude' => '31.461502756226658',
                'longitude' => '74.41432322298296',
                'full_name' => 'User Dominos',
                'username' => 'userdominos',
                'email' => 'userdominos@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'latitude' => '31.468824156702297',
                'longitude' => '74.45073473113094',
                'full_name' => 'User CCA Block',
                'username' => 'userccablock',
                'email' => 'userccablock@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'latitude' => '31.462841679221565',
                'longitude' => '74.41472408350482',
                'full_name' => 'User Al Fatah Shop',
                'username' => 'useralfatahshop',
                'email' => 'useralfatahshop@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'latitude' => '31.462931051282517',
                'longitude' => '74.41677424906347',
                'full_name' => 'User DHA Phase 5 KFC',
                'username' => 'userdhaphase5kfc',
                'email' => 'userdhaphase5kfc@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 6,
                'latitude' => '31.461415295847157',
                'longitude' => '74.41328003739943',
                'full_name' => 'User Layers Bakeshop',
                'username' => 'userlayersbakeshop',
                'email' => 'userlayersbakeshop@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 7,
                'latitude' => '31.128510989593185',
                'longitude' => '74.44141614453332',
                'full_name' => 'User Qasur',
                'username' => 'userqasur',
                'email' => 'userqasur@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 8,
                'latitude' => '31.480443625257376',
                'longitude' => '74.41543472166688',
                'full_name' => 'User Alfalah Town',
                'username' => 'useralfalahtown',
                'email' => 'useralfalahtown@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'id' => 9,
                'latitude' => '31.533204926931766',
                'longitude' => '74.31809149287956',
                'full_name' => 'User Ichra',
                'username' => 'userichra',
                'email' => 'userichra@devstudio.us',
                'password' => Hash::make('user1234'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'id' => 10,
                'latitude' => '31.533204926931766',
                'longitude' => '74.31809149287956',
                'full_name' => 'Irfan Shah',
                'username' => 'irfan',
                'email' => 'irfan@getnada.com',
                'password' => Hash::make('12345678'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'id' => 11,
                'latitude' => '31.533204926931766',
                'longitude' => '74.31809149287956',
                'full_name' => 'Munib',
                'username' => 'munib',
                'email' => 'munib@getnada.com',
                'password' => Hash::make('test12345'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+923001234567',
                'role_id' => USER_APP,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
