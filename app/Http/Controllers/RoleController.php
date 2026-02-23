<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Getting all roles
     */
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])
            ->where('id', '!=', 1)
            ->orderByDesc('id')
            ->get();
        return response()->json($roles);
    }

    /**
     * Getting a row
     */
    public function show(Role $role)
    {
        return response()->json($role->load("permissions"));
    }

    /**
     * Inserting a row
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',

            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|exists:permissions,name',
        ], [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique' => 'Ce rôle existe déjà.',

            'permissions.required' => 'Vous devez sélectionner au moins une permission.',
            'permissions.array' => 'Le format des permissions est invalide.',
            'permissions.min' => 'Vous devez sélectionner au moins une permission.',
            'permissions.*.name.required' => 'Chaque permission doit avoir un nom.',
            'permissions.*.name.exists' => 'Une ou plusieurs permissions sélectionnées sont invalides.',
        ]);

        try {
            DB::beginTransaction();

            // store
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api'
            ]);

            /**
             * Permissions
             */
            $permissions = collect($request->permissions);

            /**
             * Synchronisation des permissions
             */
            $role->syncPermissions($permissions->pluck("name"));

            DB::commit();
            Log::info("Role crée avec succès!");
            return response()->json(["message" => "Rôle crée avec succès!"], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            return response()->json($e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de d'exception", ["exception" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Updating a row
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',

            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|exists:permissions,name',
        ], [
            'permissions.required' => 'Vous devez sélectionner au moins une permission.',
            'permissions.array' => 'Le format des permissions est invalide.',
            'permissions.min' => 'Vous devez sélectionner au moins une permission.',
            'permissions.*.name.required' => 'Chaque permission doit avoir un nom.',
            'permissions.*.name.exists' => 'Une ou plusieurs permissions sélectionnées sont invalides.',
        ]);

        try {

            DB::beginTransaction();

            /**
             * Permissions
             */
            $permissions = collect($request->permissions);

            /**
             * Update role name
             */
            if ($request->name) {
                $role->update(["name" => $request->name]);
            }

            /**
             * Synchronisation des permissions
             */
            $role->syncPermissions($permissions->pluck("name"));

            DB::commit();
            return response()->json(["message" => "Mise à jour éffectuée avec succès!"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            return response()->json($e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de d'exception", ["exception" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Destroying a row
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Le rôle super-admin ne peut pas être supprimé'
            ], 401);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Ce rôle est attribué à des utilisateurs'
            ], 404);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rôle supprimé avec succès'
        ]);
    }

    /**
     * Affectation d'un role à un user
     */
    public function affectRole(Request $request)
    {
        Log::debug("Affectation de role :", ["data" => $request->all()]);

        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'role_id' => 'required|integer|exists:roles,id',
            ], [
                'user_id.required' => 'L’utilisateur est obligatoire.',
                'user_id.integer'  => 'L’identifiant de l’utilisateur doit être un nombre entier.',
                'user_id.exists'   => 'L’utilisateur sélectionné n’existe pas dans le système.',

                'role_id.required' => 'Le rôle est obligatoire.',
                'role_id.integer'  => 'L’identifiant du rôle doit être un nombre entier.',
                'role_id.exists'   => 'Le rôle sélectionné n’existe pas dans le système.',
            ]);

            $role = Role::find($validated["role_id"]);
            $user = User::find($validated["user_id"]);

            DB::beginTransaction();

            /**
             *  On supprime tous les anciens roles et on garde seulement ceux envoyés
             * */
            DB::table('model_has_roles')
                ->where('model_id', $user->id)
                // ->orWhere('role_id', $role->id)
                ->delete();

            /**
             * Affcetation
             */
            $user->assignRole($role->name);

            DB::commit();
            Log::info("Rôle affecté avec succès!");
            $user['createdAt'] = Carbon::parse($user->created_at)->locale("fr")->isoFormat("D MMMM YYYY");
            return response()->json(["message" => "Rôle affecté avec succès!", "role" => $role, "user" => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de d'exception", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Get all permissions
     */
    function getPermissions()
    {
        return response()->json(Permission::with("users")
            ->orderByDesc("id")
            ->get());
    }
}
