<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Création des rôles
        $roles = [
            'Super Administrateur',
            'Administrateur',
            'Gestionnaire',
            'Contrôlleur',
            'Superviseur',
        ];

        foreach ($roles as $role) {
            Role::create(["name" => $role, "guard_name" => "api"]);
        }
    }
}
