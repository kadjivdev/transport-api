<?php

namespace App\Http\Controllers;

use App\Http\Requests\CamionRequest;
use App\Http\Resources\CamionResource;
use App\Models\Camion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CamionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les Camions");
        $Camions = Camion::latest()->get();

        $data = CamionResource::collection($Camions);
        if ($Camions->isEmpty()) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CamionRequest $request)
    {
        try {
            DB::beginTransaction();
            Camion::create($request->validated());

            DB::commit();
            Log::info("Camion crée avec succès");
            return response()->json(["message" => "Camion crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion de Camion", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Camion $Camion)
    {
        Log::debug("The Camion called!", ["data" => $Camion]);
        return response()->json($Camion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CamionRequest $request, Camion $Camion)
    {
        Log::info("Updating Camion ...", ["Camion" => $Camion]);
        try {
            DB::beginTransaction();
            $Camion->update($request->validated());

            $Camion->refresh();

            DB::commit();
            Log::info("Camion modifié avec succès");
            return response()->json(["message" => "Camion modifié avec succès", "data" => $Camion]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification du Camion", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Camion $Camion)
    {
        try {
            DB::beginTransaction();
            $Camion->delete();
            DB::commit();
            return response()->json(["message" => "Camion supprimé.e avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du Camion", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
