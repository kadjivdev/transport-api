<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepenseRequest;
use App\Http\Resources\DepenseRousource;
use App\Models\DepenseLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DepenseLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de toutes les depenses");
        $depenses = DepenseLocation::latest()->get();

        $data = DepenseRousource::collection($depenses);
        if ($depenses->isEmpty()) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepenseRequest $request)
    {
        Log::info("Début d'insertion des dépenses", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $depense = DepenseLocation::create($request->validated());

            Log::debug("The depense created :", ["data" => $depense]);

            DB::commit();
            Log::info("Depense crée.e avec succès");
            return response()->json(["message" => "Dépense créee avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion de la dépense", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DepenseLocation $depense)
    {
        Log::debug("The depense called!", ["data" => $depense]);
        return response()->json($depense->load(["location", "createdBy", "validatedBy"]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepenseRequest $request, DepenseLocation $depense)
    {
        Log::info("Updating dépense ...", ["data" => $request->validated()]);
        try {
            DB::beginTransaction();

            // updating ....
            $depense->update($request->validated());

            $depense->refresh();

            DB::commit();
            Log::info("Dépense modifiée.e avec succès");
            return response()->json(["message" => "Dépense modifiée.e avec succès", "data" => $depense]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification de la dépense", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepenseLocation $depense)
    {
        Log::info("Deleting depense ...", ["depense" => $depense]);

        try {
            DB::beginTransaction();

            // reglement
            $depense->delete();
            DB::commit();
            return response()->json(["message" => "Dépense supprimée avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression de la dépense", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'une location
     */
    public function validate(DepenseLocation $depense)
    {
        try {
            DB::beginTransaction();

            $depense->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Dépense validée avec succès!");
            return response()->json(["message" => "Dépense validée avec succès", "data" => $depense->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation de la dépense :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
