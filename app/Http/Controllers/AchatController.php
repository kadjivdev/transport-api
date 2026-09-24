<?php

namespace App\Http\Controllers;

use App\Http\Requests\AchatRequest;
use App\Http\Resources\AchatResource;
use App\Models\Achat;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchatController extends Controller
{
    /**
     * Recuperation des achats.
     */
    public function index()
    {
        try {
            $achats = Achat::with([
                "product",
                "fournisseur",
                "camion",
                "createdBy",
                "validatedBy",
            ])->latest()->get();

            return response()->json([
                "data" => AchatResource::collection($achats),
                "message" => "Achats récupérés avec succès!",
            ]);
        } catch (Exception $e) {
            Log::debug("Erreur lors de la récupération des achats", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Recuperation des achats validés.
     */
    public function achatValidated()
    {
        try {
            $achats = Achat::query()
                ->whereNotNull("validated_by") //seuls les achats validés
                ->latest()
                ->select(["id", "code"])
                ->get();

            return response()->json([
                "data" => $achats,
                "message" => "Achats validés récupérés avec succès!",
            ]);
        } catch (Exception $e) {
            Log::debug("Erreur lors de la récupération des achats", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre un achat.
     */
    public function store(AchatRequest $request)
    {
        try {
            DB::beginTransaction();

            $achat = Achat::create($request->validated());
            $achat->load(["product", "fournisseur", "camion", "createdBy", "validatedBy"]);

            DB::commit();

            return response()->json([
                "data" => $achat,
                "message" => "Achat ajouté avec succès",
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreur de validation lors de l'insertion de l'achat", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de l'insertion de l'achat", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche un achat.
     */
    public function show(Achat $achat)
    {
        $achat->load(["product", "fournisseur", "camion", "createdBy", "validatedBy"]);
        return response()->json(["data" => $achat]);
    }

    /**
     * Met à jour un achat.
     */
    public function update(AchatRequest $request, Achat $achat)
    {
        try {
            DB::beginTransaction();

            $achat->update($request->validated());
            $achat->load(["product", "fournisseur", "camion", "createdBy", "validatedBy"]);

            DB::commit();

            return response()->json([
                "data" => $achat,
                "message" => "Achat mis à jour avec succès",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreur de validation lors de la mise à jour de l'achat", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la mise à jour de l'achat", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Valider un achat.
     */
    public function validateAchat(Achat $achat)
    {
        try {
            DB::beginTransaction();

            $achat->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "data" => $achat,
                "message" => "Achat validé avec succès",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la validation de l'achat", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime un achat.
     */
    public function destroy(Achat $achat)
    {
        try {
            DB::beginTransaction();
            $achat->delete();
            DB::commit();

            return response()->json(["message" => "Achat supprimé avec succès"]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la suppression de l'achat", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
