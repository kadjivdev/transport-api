<?php

namespace App\Http\Controllers;

use App\Http\Requests\VenteRequest;
use App\Http\Resources\VenteResource;
use App\Models\Vente;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VenteController extends Controller
{
    /**
     * Recuperation des ventes.
     */
    public function index()
    {
        try {
            $ventes = Vente::with([
                "achat",
                "client",
                "camion",
                "createdBy",
                "validatedBy",
            ])->latest()->get();

            return response()->json([
                "data" => VenteResource::collection($ventes),
                "message" => "Ventes récupérées avec succès!",
            ]);
        } catch (Exception $e) {
            Log::debug("Erreur lors de la récupération des ventes", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Recuperation des ventes validées.
     */
    public function venteValidated()
    {
        try {
            $ventes = Vente::query()
                ->whereNotNull("validated_by") //seuls les achats validés
                ->latest()
                // ->select(["id", "code"])
                ->get();

            return response()->json([
                "data" => VenteResource::collection($ventes),
                "message" => "Ventes validées récupérés avec succès!",
            ]);
        } catch (Exception $e) {
            Log::debug("Erreur lors de la récupération des ventes", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre une vente.
     */
    public function store(VenteRequest $request)
    {
        try {
            Log::info("Les dats entrant:", ["data" => $request->validated()]);
            DB::beginTransaction();

            $vente = Vente::create($request->validated());
            $vente->load(["achat", "client", "camion", "createdBy", "validatedBy"]);

            DB::commit();

            return response()->json([
                "data" => $vente,
                "message" => "Vente ajoutée avec succès",
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de l'insertion de la vente", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche une vente.
     */
    public function show(Vente $vente)
    {
        $vente->load(["achat", "client", "camion", "createdBy", "validatedBy"]);

        return response()->json(["data" => new VenteResource($vente)]);
    }

    /**
     * Met à jour une vente.
     */
    public function update(VenteRequest $request, Vente $vente)
    {
        try {
            DB::beginTransaction();

            $vente->update($request->validated());
            $vente->load(["achat", "client", "camion", "createdBy", "validatedBy"]);

            DB::commit();

            return response()->json([
                "data" => new VenteResource($vente),
                "message" => "Vente mise à jour avec succès",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la mise à jour de la vente", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Valider une vente.
     */
    public function validateVente(Vente $vente)
    {
        Log::info("Validation de la Vente $vente->code ");
        try {
            DB::beginTransaction();

            $vente->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "data" => $vente,
                "message" => "Vente validée avec succès",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la validation de la vente", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime une vente.
     */
    public function destroy(Vente $vente)
    {
        try {
            DB::beginTransaction();
            $vente->delete();
            DB::commit();

            return response()->json(["message" => "Vente supprimée avec succès"]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreur lors de la suppression de la vente", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
