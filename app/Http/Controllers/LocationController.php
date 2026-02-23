<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use function Symfony\Component\Clock\now;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de toutes les locations");
        $locations = Location::latest()->get();

        $data = LocationResource::collection($locations);
        if ($locations->isEmpty()) {
            return response()->json($data, 204);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocationRequest $request)
    {
        Log::info("Début d'insertion des locations", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $location = Location::create($request->validated());

            Log::debug("The location created :", ["data" => $location]);

            // details
            if ($request->details) {
                $location->details()
                    ->createMany($request->details);
            }

            DB::commit();
            Log::info("Location créee avec succès");
            return response()->json(["message" => "Location créee avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion de la location", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        Log::debug("The location called!", ["data" => $location]);
        return response()->json($location->load(["client", "type", "details", "createdBy", "validatedBy"]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocationRequest $request, Location $location)
    {
        Log::info("Updating location ...", ["location" => $location, "data" => $request->all()]);
        try {
            DB::beginTransaction();

            // suppressison des détails
            $location->details()->delete();

            // updating ....
            $location->update($request->validated());

            // details
            if ($location->details->isNotEmpty()) {
                $location->details()
                    ->createMany($request->details);
            }

            $location->refresh();

            DB::commit();
            Log::info("Location modifiée avec succès");
            return response()->json(["message" => "Location modifiee avec succès", "data" => $location]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification de la location", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        try {
            DB::beginTransaction();

            // suppressison des détails
            $location->details()->delete();

            // location
            $location->delete();
            DB::commit();
            return response()->json(["message" => "Location supprimée avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression de la location", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'une location
     */
    public function validate(Location $location)
    {
        try {
            DB::beginTransaction();

            $location->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Location validée avec succès!");
            return response()->json(["message" => "Location validée avec succès", "data" => $location->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation de la location :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
