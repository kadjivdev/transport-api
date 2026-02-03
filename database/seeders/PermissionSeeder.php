<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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
            "Utilisateurs" => $this->createCrudValidatePermissions("utilisateurs", "utilisateur"),
            "Rôles" => array_merge(
                ["Attribuer des roles aux utilisateurs" => "role.assign"],
                ["Attribuer des permissions aux rôles" => "permission.assign"],
                $this->createCrudValidatePermissions("rôles", "role"),
            ),

            "Camions" => $this->createCrudValidatePermissions("camions", "camion"),
            'Clients' => $this->createCrudValidatePermissions('clients', 'client'),
            'Locations' => $this->createCrudValidatePermissions('locations', 'location'),
            'Règlements' => $this->createCrudValidatePermissions('reglements', 'reglement'),
            'Dépenses' => $this->createCrudValidatePermissions('depenses', 'depense'),
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
