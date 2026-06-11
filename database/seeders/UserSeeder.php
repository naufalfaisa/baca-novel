<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'role' => 'admin'
            ],
            [
                'name' => 'Author',
                'email' => 'author@example.com',
                'role' => 'author'
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'role' => 'user'
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => Hash::make('password')])
            );
        }
    }
}
