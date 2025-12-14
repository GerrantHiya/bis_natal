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
            // Admins (can view logs)
            ['username' => 'gerrant', 'name' => 'Gerrant', 'is_admin' => true],
            ['username' => 'didik', 'name' => 'Didik', 'is_admin' => true],
            
            // Regular users
            ['username' => 'yesaya', 'name' => 'Yesaya', 'is_admin' => false],
            ['username' => 'aldy', 'name' => 'Aldy', 'is_admin' => false],
            ['username' => 'yezki', 'name' => 'Yezki', 'is_admin' => false],
            ['username' => 'rita', 'name' => 'Rita', 'is_admin' => false],
            ['username' => 'ike', 'name' => 'Ike', 'is_admin' => false],
            ['username' => 'sri', 'name' => 'Sri', 'is_admin' => false],
            ['username' => 'novi', 'name' => 'Novi', 'is_admin' => false],
            ['username' => 'matthew', 'name' => 'Matthew', 'is_admin' => false],
            ['username' => 'daniel', 'name' => 'Daniel', 'is_admin' => false],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['username'] . '@bissm.local',
                    'password' => Hash::make($userData['username']), // Password = username
                    'is_admin' => $userData['is_admin'],
                ]
            );
        }
    }
}
