<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ["libele"=>"Concassés"],
            ["libele"=>"Sable de marin"],
            ["libele"=>"Laterite"],
            ["libele"=>"Moellon"],
        ];

        // desactivation temporaire des contraintes
        Schema::disableForeignKeyConstraints();

        // truncate de la table
        Product::truncate();

        // insersion many
        Product::insert($products);
    }
}
