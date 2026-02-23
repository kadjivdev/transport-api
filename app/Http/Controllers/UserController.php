<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    function __construct()
    {
        // Log::debug("Les cookies actuels : ", ["data" => request()->cookie("access_token")]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les utilisateurs");
        $users = User::latest()->get();

        $data = UserResource::collection($users);
        if ($users->isEmpty()) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        try {
            Log::debug("Creating new user...", ["data" => $request->validated()]);

            DB::beginTransaction();
            User::create($request->validated());

            DB::commit();
            Log::info("Utilisateur crée avec succès");
            return response()->json(["message" => "Utilisateur crée avec succès"], 201);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion d'utilisateur", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        Log::debug("The user called!", ["data" => $user->load("roles")]);
        return response()->json($user->load("roles"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        Log::info("Updating user ...", ["user" => $user]);
        try {
            DB::beginTransaction();
            
            $user->update($request->validated());

            $user->refresh();

            DB::commit();
            Log::info("Utilisateur modifié.e avec succès");
            return response()->json(["message" => "Utilisateur modifié.e avec succès", "data" => $user]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification d'utilisateur", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();
            $user->delete();
            DB::commit();
            return response()->json(["message" => "Utilisateur supprimé.e avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression de l'utilisateur", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
