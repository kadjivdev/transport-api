<?php

namespace App\Http\Controllers;

use App\Models\ClientAcompte;
use App\Http\Requests\ClientAcompteRequest;
use App\Http\Resources\ClientAccompteResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientAcompteController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les accomptes");
        $accomptes = ClientAcompte::latest()->get();

        $data = ClientAccompteResource::collection($accomptes);
        if ($accomptes->isEmpty()) {
            return response()->json($data, 204);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientAcompteRequest $request)
    {
        Log::info("Début d'insertion de l'accompte", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $acompte = ClientAcompte::create($request->validated());

            Log::debug("The acompte created :", ["data" => $acompte]);

            DB::commit();
            Log::info("Acompte crée avec succès");
            return response()->json(["message" => "Acompte crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion de l'acompte", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientAcompteRequest $request, ClientAcompte $acompte)
    {
        Log::info("Updating acompte ...", ["acompte" => $acompte, "data" => $request->all()]);
        try {
            DB::beginTransaction();

            // updating ....
            Log::debug("Validated request : ", ["data" => $request->validated()]);

            $acompte->update($request->validated());

            DB::commit();
            Log::info("Acompte modifié avec succès");
            return response()->json(["message" => "acompte modifiee avec succès", "data" => $acompte]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification de la reglement", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientAcompte $acompte)
    {
        Log::info("Deleting acompte ...", ["acompte" => $acompte]);

        try {
            DB::beginTransaction();

            // acompte
            $acompte->delete();
            DB::commit();
            return response()->json(["message" => "Acompte supprimé avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression de l'acompte", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'un acompte
     */
    public function validate(ClientAcompte $acompte)
    {
        try {
            DB::beginTransaction();

            $acompte->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Acompte validé avec succès!");
            return response()->json(["message" => "Acompte validé avec succès", "data" => $acompte->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation de l'acompte :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
