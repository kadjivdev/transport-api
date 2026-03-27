<?php

namespace App\Http\Controllers;

use App\Http\Requests\TvaRequest;
use App\Http\Resources\TvaResource;
use App\Models\Tva;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TvaController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les tvas");
        $tvas = Tva::latest()->get();

        $data = TvaResource::collection($tvas);
        if ($tvas->isEmpty()) {
            return response()->json($data, 204);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TvaRequest $request)
    {
        Log::info("Début d'insertion des tvas", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $tva = Tva::create($request->validated());

            Log::debug("The tva created :", ["data" => $tva]);

            DB::commit();
            Log::info("tva crée avec succès");
            return response()->json(["message" => "Tva crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion du tva", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TvaRequest $request, Tva $tva)
    {
        Log::info("Updating tva ...", ["tva" => $tva, "data" => $request->all()]);
        try {
            DB::beginTransaction();

            // updating ....
            Log::debug("Validated request : ", ["data" => $request->validated()]);

            $tva->update($request->validated());

            DB::commit();
            Log::info("Tva modifié avec succès");
            return response()->json(["message" => "tva modifiée avec succès", "data" => $tva]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification du tva", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tva $tva)
    {
        Log::info("Deleting tva ...", ["tva" => $tva]);

        try {
            DB::beginTransaction();

            // back
            $tva->delete();
            DB::commit();
            return response()->json(["message" => "Tva supprimé avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du tva", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'un tva
     */
    public function validate(Tva $tva)
    {
        try {
            DB::beginTransaction();

            $tva->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Tva validé avec succès!");
            return response()->json(["message" => "Tva validé avec succès", "data" => $tva->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation du tva :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
