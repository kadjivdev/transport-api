<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Recuperation des produits
     */
    public function index()
    {
        Log::info("Début de recuperation des produits");
        try {
            $products = Product::orderByDesc("id")->get();
            return response()->json(["data" => ProductResource::collection($products), "message" => "Produits récupérés avec succès!"]);
        } catch (\Exception $e) {
            Log::debug("Erreure lors de la récupération des produits", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Store product
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::create($request->validated());
            DB::commit();
            return response()->json(["data" => $product, "message" => "Produit ajouté avec succès"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de l'insersion du produit", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de l'insersion du produit", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update product
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();
            $product->update($request->validated());
            DB::commit();
            return response()->json(["data" => new ProductResource($product), "message" => "Produit mis à jour avec succès"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la mise à jour du produit", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de la mise à jour du produit", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();
            $product->delete();
            DB::commit();
            return response()->json(["message" => "Produit supprimé avec succès"]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure d'exception lors de la suppression du produit", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
