<?php

namespace Database\Seeders;

use App\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        LocationType::insert([
            ["libelle" => "Par voyage", "description" => "Location par voyage", "price" => 0],
            ["libelle" => "Journalière", "description" => "Location par jour", "price" => 0],
            ["libelle" => "Par tonnage", "description" => "Location par tonnage", "price" => 0],
        ]);
    }
}
