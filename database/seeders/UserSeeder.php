<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'is_admin' => true,
            'mobile' => '09123456789',
            'status' => 'active'
        ]);
    }
}
