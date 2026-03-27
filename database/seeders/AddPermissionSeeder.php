<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AddPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private function createCrudValidatePermissions($name, $permission)
    {
        return [
            "Voir les $name" => "$permission.view",
            "Créer des $name" => "$permission.create",
            "Modifier les $name" => "$permission.edit",
            "Supprimer des $name" => "$permission.delete",
            "Valider les $name" => "$permission.validate",
        ];
    }

    public function run(): void
    {
        $permissions_groups = [
            'Tvas' => $this->createCrudValidatePermissions('tvas', 'tva'),
        ];

        foreach ($permissions_groups as $group => $permissions) {
            foreach ($permissions as $description => $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'api'],
                    ['name' => $permission, 'group_name' => $group, 'description' => $description]
                );
            }
        }
    }
}
