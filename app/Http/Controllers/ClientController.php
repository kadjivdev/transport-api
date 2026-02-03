<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Chargement de tous les clients");
        $clients = Client::latest()->get();

        $data = ClientResource::collection($clients);
        if ($clients->isEmpty()) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request)
    {
        try {
            DB::beginTransaction();
            Client::create($request->validated());

            DB::commit();
            Log::info("Client crée avec succès");
            return response()->json(["message" => "Client.e crée.e avec succès"]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation", ["errors" => $e->errors()]);
            DB::rollBack();
            return response()->json(["errors" => $e->errors()]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de l'insersion de client", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        Log::debug("The client called!", ["data" => $client]);
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client)
    {
        Log::info("Updating client ...", ["client" => $client]);
        try {
            DB::beginTransaction();
            $client->update($request->validated());

            $client->refresh();

            DB::commit();
            Log::info("Client modifié.e avec succès");
            return response()->json(["message" => "Utilisateur modifié.e avec succès", "data" => $client]);
        } catch (\Exception $e) {
            Log::debug("Une erreure est survenue lors de la modification du client", ["error" => $e->getMessage()]);
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        try {
            DB::beginTransaction();
            $client->delete();
            DB::commit();
            return response()->json(["message" => "Client supprimé.e avec succcès!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Erreure survenue lors de la suppression du client", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
