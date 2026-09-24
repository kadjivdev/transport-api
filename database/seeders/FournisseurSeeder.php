<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fournisseurs = [
            ["libele" => "Adéoti"],
            ["libele" => "Lit gravière"],
            ["libele" => "BCG"],
            ["libele" => "HHB"],
        ];

        // truncate de la table
        Fournisseur::truncate();

        // insersion many
        Fournisseur::insert($fournisseurs);
    }
}
