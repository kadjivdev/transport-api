<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(LocationType::orderByDesc("id")->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            LocationType::create($request->only("libelle", "description"));

            DB::commit();
            Log::info("Type crée avec succès");
            return response()->json(["message" => "Type crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion du type", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LocationType $location_type)
    {
        Log::debug("The type called!", ["data" => $location_type]);
        return response()->json($location_type);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LocationType $location_type)
    {
        Log::info("Updating type ...", ["type" => $location_type]);
        try {
            DB::beginTransaction();
            $location_type->update($request->all());

            $location_type->refresh();

            DB::commit();
            Log::info("Type modifié avec succès");
            return response()->json(["message" => "Type modifié avec succès", "data" => $location_type]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification du type", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LocationType $location_type)
    {
        try {
            DB::beginTransaction();
            $location_type->delete();
            DB::commit();
            return response()->json(["message" => "Type supprimé avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du type", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
