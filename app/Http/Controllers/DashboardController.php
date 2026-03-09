<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Log::debug("Dashbaord called ....");
        
        $locations = Location::whereNotNull("validated_at")->get();

        $totaux = [
            "total_count" => $locations->count(),
            "total_amount" => number_format($locations->sum("total_amount"), 2, ",", " "),
            "total_regler" => number_format($locations->sum("regler"), 2, ",", " "),
            "total_reste_a_regler" => number_format($locations->sum("reste_a_regler"), 2, ",", " "),
            "total_depense_amount" => number_format($locations->sum("depense_amount"), 2, ",", " "),
        ];

        return response()
            ->json([
                "totaux" => $totaux,
            ]);
    }
}
