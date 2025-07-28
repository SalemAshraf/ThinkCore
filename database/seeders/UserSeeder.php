<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Salem Ashraf',
            'email' => 'salemashraf2002@gmail.com',
            'password' => bcrypt('123456123456'),
            'role' => 'student',
            ],
            ['name' => 'Instructor Name',
            'email' => 'Instructor@gmail.com',
            'password' => bcrypt('123456123456'),
            'role' => 'instructor',
            ],
        ];

        User::insert($users);
    }
}
