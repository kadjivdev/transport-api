<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AffectFirstRoleToSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::find(1);
        $role->users()->attach(1); // Affecter le rôle de super admin à l'administrateur
    }
}
