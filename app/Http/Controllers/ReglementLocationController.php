<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReglementRequest;
use App\Http\Resources\ReglementResource;
use App\Models\ReglementLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReglementLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les reglements");
        $reglements = ReglementLocation::latest()->get();

        $data = ReglementResource::collection($reglements);
        if ($reglements->isEmpty()) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReglementRequest $request)
    {
        Log::info("Début d'insertion des reglements", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $reglement = ReglementLocation::create($request->validated());

            Log::debug("The reglement created :", ["data" => $reglement]);

            DB::commit();
            Log::info("Reglement crée avec succès");
            return response()->json(["message" => "Reglement crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion du reglement", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ReglementLocation $reglement)
    {
        Log::debug("The reglement called!", ["data" => $reglement]);
        return response()->json($reglement->load(["location", "createdBy", "validatedBy"]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReglementRequest $request, ReglementLocation $reglement)
    {
        Log::info("Updating reglement ...", ["reglement" => $reglement, "data" => $request->all()]);
        try {
            DB::beginTransaction();

            // updating ....
            $reglement->update($request->validated());

            $reglement->refresh();

            DB::commit();
            Log::info("Reglement modifié avec succès");
            return response()->json(["message" => "reglement modifiee avec succès", "data" => $reglement]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification de la reglement", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReglementLocation $reglement)
    {
        Log::info("Deleting reglement ...", ["reglement" => $reglement]);

        try {
            DB::beginTransaction();

            // reglement
            $reglement->delete();
            DB::commit();
            return response()->json(["message" => "Reglement supprimé avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du reglement", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'une location
     */
    public function validate(ReglementLocation $reglement)
    {
        try {
            DB::beginTransaction();

            $reglement->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Reglement validé avec succès!");
            return response()->json(["message" => "Reglement validé avec succès", "data" => $reglement->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation du reglement :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
