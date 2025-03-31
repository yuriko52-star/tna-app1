<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        ];
        DB::table('users')->insert($data);

        $data = [
            'name' => '浅野長政',
            'email' => 'asano@gmail.com',
            'password' => Hash::make('naganaganaga'),
            'role' => 'user',
        ];
        DB::table('users')->insert($data);

        $data = [
            'name' => 'クレオパトラ',
            'email' => 'cleo@gmail.com',
            'password' => Hash::make('patrapatra'),
            'role' => 'user',
        ];
        DB::table('users')->insert($data);
    }
}
