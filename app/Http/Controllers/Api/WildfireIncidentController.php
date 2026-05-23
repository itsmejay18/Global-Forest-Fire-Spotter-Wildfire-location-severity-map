<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WildfireIncident;
use App\Http\Resources\WildfireIncidentResource;
use Illuminate\Http\Request;

class WildfireIncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WildfireIncident::query();

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidents = $query->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => WildfireIncidentResource::collection($incidents),
        ]);
    }
}
