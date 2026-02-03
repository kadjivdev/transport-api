<?php

namespace Database\Seeders;

use App\Models\Camion;
use Illuminate\Database\Seeder;

class CamionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Camion::insert([
            ["libelle" => "Camion 1", "immatriculation" => "BX456DF"],
            ["libelle" => "Camion 2", "immatriculation" => "WX676EA"],
        ]);
    }
}
