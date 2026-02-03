<?php

namespace Database\Seeders;

use App\Models\LocationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            ["libelle" => "Par voyage", "description" => "Location par voyage"],
            ["libelle" => "Journalière", "description" => "Location par jour"],
            ["libelle" => "Par tonnage", "description" => "Location par tonnage"],
        ]);
    }
}
