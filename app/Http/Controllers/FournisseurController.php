<?php

namespace App\Http\Controllers;

use App\Http\Requests\FournisseurRequest;
use App\Http\Resources\FournisseurResource;
use App\Models\Fournisseur;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FournisseurController extends Controller
{
    /**
     * Recuperation des fournisseurs
     */
    public function index()
    {
        try {
            $fournisseurs = Fournisseur::orderByDesc("id")->get();
            return response()->json(["data" => FournisseurResource::collection($fournisseurs), "message" => "Fournisseurs récupérés avec succès!"]);
        } catch (\Exception $e) {
            Log::debug("Erreure lors de la récupération des fournisseur", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Store fournisseur
     */
    public function store(FournisseurRequest $request)
    {
        try {
            DB::beginTransaction();
            $fournisseur = Fournisseur::create($request->validated());
            DB::commit();
            return response()->json(["data" => $fournisseur, "message" => "Fournisseur ajouté avec succès"],201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de l'insersion du fournisseur", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de l'insersion du fournisseur", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update fournisseur
     */
    public function update(FournisseurRequest $request, Fournisseur $fournisseur)
    {
        try {
            DB::beginTransaction();
            $fournisseur->update($request->validated());
            $fournisseur->refresh();
            DB::commit();
            return response()->json(["data" => $fournisseur, "message" => "Fournisseur mis à jour avec succès"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la mise à jour du fournisseur", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de la mise à jour du fournisseur", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Delete fournisseur
     */
    public function destroy(Fournisseur $fournisseur)
    {
        try {
            DB::beginTransaction();
            $fournisseur->delete();
            DB::commit();
            return response()->json(["message" => "Fournisseur supprimé avec succès"]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de la suppression du fournisseur", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
