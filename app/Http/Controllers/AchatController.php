<?php

namespace App\Http\Controllers;

use App\Http\Requests\AchatRequest;
use App\Http\Resources\AchatResource;
use App\Models\Achat;
use App\Models\Fournisseur;
use Exception;
use Illuminate\Http\Request;
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
        Log::info("Récupération des achats validés");

        try {
            $achats = Achat::query()
                ->whereNotNull("validated_at") //seuls les achats validés
                ->latest()
                ->get();

            Log::debug("Récupération des achats validés", ["data" => $achats]);

            return response()->json([
                "data" => AchatResource::collection($achats),
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
        Log::debug("Données reçues pour l'insertion de l'achat", ["data" => $request->all()]);
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
                "validated_by" => Auth::user()?->id,
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

    /**
     * Statistique par achat.
     */
    public function statistiquesAchat(Request $request)
    {
        try {

            if (!$request->fournisseur_id) {
                return response()
                    ->json([]);
            }

            Log::debug("Les datas : ", ["datas" => $request->all()]);
            $data = Achat::with([
                "product",
                "fournisseur",
                "camion",
                "createdBy",
                "validatedBy",
                "ventes" => fn ($query) => $query->whereNotNull("validated_at"),
            ])->latest()
                ->whereNotNull("validated_at")
                ->where("fournisseur_id", $request->fournisseur_id)
                ->get();

            $achats = AchatResource::collection($data);

            Log::debug("Achats filtrés :", ["data" => $achats]);

            $totalMontant = $data->sum(fn (Achat $achat) => $achat->montant);
            $totalVendue = $data->sum(
                fn (Achat $achat) => $achat->ventes->sum(fn ($vente) => $vente->amount)
            );
            $totaux = [
                "total_montant" => number_format($totalMontant, 2, ",", " "),
                "total_vendue" => number_format($totalVendue, 2, ",", " "),
                "total_reste" => number_format($totalMontant - $totalVendue, 2, ",", " "),
            ];

            Log::debug("Achats totaux :", ["data" => $totaux]);

            return response()->json([
                "achats" => $achats,
                "totaux" => $totaux,
                "fournisseur" => Fournisseur::find($request->fournisseur_id),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug("Erreur de validation lors de la statistique", [
                "data" => $e->errors()
            ]);

            return response()->json($e->errors(), 422);
        }
    }
}
