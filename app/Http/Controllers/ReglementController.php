<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReglementRequest;
use App\Http\Resources\ReglementResource;
use App\Models\Reglement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReglementController extends Controller
{
    public function index()
    {
        try {
            $reglements = Reglement::with("vente")->latest()->get();

            return response()->json([
                "data" => ReglementResource::collection($reglements),
                "message" => "Règlements récupérés avec succès!",
            ]);
        } catch (Exception $e) {
            Log::debug("Erreur lors de la récupération des règlements", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function store(ReglementRequest $request)
    {
        try {
            DB::beginTransaction();

            $reglement = Reglement::create($request->validated());
            $reglement->load("vente");

            DB::commit();

            return response()->json([
                "data" => new ReglementResource($reglement),
                "message" => "Règlement ajouté avec succès",
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de l'insertion du règlement", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function show(Reglement $reglement)
    {
        $reglement->load("vente");

        return response()->json(["data" => new ReglementResource($reglement)]);
    }

    public function update(ReglementRequest $request, Reglement $vente_reglement)
    {
        Log::info("Données validées des reglement ", ["data" => $request->validated()]);
        try {
            DB::beginTransaction();

            $vente_reglement->update($request->validated());
            $vente_reglement->refresh();
            DB::commit();

            return response()->json([
                "data" => $vente_reglement,
                "message" => "Règlement mis à jour avec succès",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la mise à jour du règlement", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Valider un reglement.
     */
    public function validateReglement(Reglement $reglement)
    {
        Log::info("Debut de validation du reglement", ["data" => $reglement]);
        try {
            DB::beginTransaction();

            $reglement->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);
            $reglement->refresh();
            DB::commit();

            return response()->json([
                "data" => $reglement,
                "message" => "Reglement validé avec succès",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la validation du reglement", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * 
     */
    public function destroy(Reglement $vente_reglement)
    {
        try {
            DB::beginTransaction();
            $vente_reglement->delete();
            $vente_reglement->refresh();
            DB::commit();

            return response()->json(["message" => "Règlement supprimé avec succès"]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la suppression du règlement", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
