<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class NewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            FournisseurSeeder::class
        ]);
        Log::info("New seeder inserée");
    }
}
