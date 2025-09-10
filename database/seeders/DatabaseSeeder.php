<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

   User::factory()->create([
    'name' => 'TestUser',
    'email' => 'testUser@example.com',
    'email_verified_at' => null,
    'password' => bcrypt('password123'),
    'login' => 'testadmin3',
    'profil_id' => 5,
     'created_at' => null,
    'updated_at' => null,
]);



    }
}
