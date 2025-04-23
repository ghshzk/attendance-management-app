<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一般ユーザー
        $param = [
            'name' => '山田 太郎',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '鈴木 二郎',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ];
        User::create($param);

        //管理者ユーザー
        $param = [
            'name' => '管理者 ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
        ];
        User::create($param);
    }
}
