<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Admin',
                'email' => 'kemenag@kemenag.go.id',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
        ];

        foreach ($data as $item) {
            \App\Models\User::create($item);
        }
    }
}
