<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'name' => '斎藤道三',
            'email' => 'dousan@gmail.com',
            'password' => Hash::make('mamushinooyazi'),
            'role' => 'admin',
            'email_verified_at' => null,
        ];
        DB::table('users')->insert($data);

        $data = [
            'name' => '浅野長政',
            'email' => 'asano@gmail.com',
            'password' => Hash::make('naganaganaga'),
            'role' => 'user',
            'email_verified_at' => Carbon::now(),
        ];
        DB::table('users')->insert($data);

        $data = [
            'name' => 'クレオパトラ',
            'email' => 'cleo@gmail.com',
            'password' => Hash::make('patrapatra'),
            'role' => 'user',
            'email_verified_at' => Carbon::now(),
        ];
        DB::table('users')->insert($data);
    }
}
