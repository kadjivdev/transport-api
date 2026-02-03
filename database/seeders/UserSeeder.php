<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'administrateur',
            'email' => 'admin@gmail.com',
            'password' => "transport@2026", //transport@2026
        ]);
    }
}
