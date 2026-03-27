<?php

namespace App\Http\Controllers;

use App\Http\Requests\FondBackRequest;
use App\Http\Resources\FondBackResource;
use App\Models\FondBack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FondBackController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les retours de fond");
        $backs = FondBack::latest()->get();

        $data = FondBackResource::collection($backs);
        if ($backs->isEmpty()) {
            return response()->json($data, 204);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FondBackRequest $request)
    {
        Log::info("Début d'insertion du retour des fonds", ["data" => $request->all()]);
        Log::info("The user connected", ["user" => Auth::user()]);
        try {
            DB::beginTransaction();
            $back = FondBack::create($request->validated());

            Log::debug("The back created :", ["data" => $back]);

            DB::commit();
            Log::info("Retour de fond crée avec succès");
            return response()->json(["message" => "Retour de fond crée avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion du retour de fond", ["error" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FondBackRequest $request, FondBack $back)
    {
        Log::info("Updating back ...", ["back" => $back, "data" => $request->all()]);
        try {
            DB::beginTransaction();

            // updating ....
            Log::debug("Validated request : ", ["data" => $request->validated()]);

            $back->update($request->validated());

            DB::commit();
            Log::info("Retour de fond modifié avec succès");
            return response()->json(["message" => "Retour de fond modifiée avec succès", "data" => $back]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification du retour de fond", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FondBack $back)
    {
        Log::info("Deleting back ...", ["back" => $back]);

        try {
            DB::beginTransaction();

            // back
            $back->delete();
            DB::commit();
            return response()->json(["message" => "Retour de fond supprimé avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du retour de fond", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /***
     * Validation d'un retour de fond
     */
    public function validate(FondBack $back)
    {
        try {
            DB::beginTransaction();

            $back->update([
                "validated_at" => now(),
                "validated_by" => Auth::id()
            ]);

            DB::commit();
            Log::info("Retour de fond validé avec succès!");
            return response()->json(["message" => "Retour de fond validé avec succès", "data" => $back->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est sruvenue lors de la validation du retour de fond :", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
