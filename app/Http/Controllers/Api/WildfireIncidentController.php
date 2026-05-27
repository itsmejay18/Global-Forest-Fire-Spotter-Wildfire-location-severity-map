<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WildfireIncident;
use App\Http\Resources\WildfireIncidentResource;
use App\Services\WildfireCsvLoader;
use Illuminate\Http\Request;

class WildfireIncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $csvPath = null;
        if (is_file(base_path('datasets.csv'))) {
            $csvPath = base_path('datasets.csv');
        } elseif (is_file(storage_path('app/wildfires_sample.csv'))) {
            $csvPath = storage_path('app/wildfires_sample.csv');
        }

        if ($csvPath !== null) {
            $loader = new WildfireCsvLoader();
            $incidents = $loader->load($csvPath);

            if ($request->filled('month')) {
                $month = (int) $request->month;
                $incidents = array_values(array_filter($incidents, fn ($i) => (int) ($i['month'] ?? 0) === $month));
            }

            if ($request->filled('year')) {
                $year = (int) $request->year;
                $incidents = array_values(array_filter($incidents, fn ($i) => (int) ($i['year'] ?? 0) === $year));
            }

            if ($request->filled('severity')) {
                $severity = (string) $request->severity;
                $incidents = array_values(array_filter($incidents, fn ($i) => (string) ($i['severity'] ?? '') === $severity));
            }

            $features = array_map(function ($incident) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            (float) $incident['longitude'],
                            (float) $incident['latitude'],
                        ],
                    ],
                    'properties' => [
                        'id' => (int) $incident['id'],
                        'name' => (string) $incident['name'],
                        'burned_area' => (float) $incident['burned_area'],
                        'fire_date' => (string) $incident['fire_date'],
                        'severity' => (string) $incident['severity'],
                        'country' => (string) $incident['country'],
                        'duration' => (int) $incident['duration'],
                        'month' => (int) $incident['month'],
                        'year' => (int) $incident['year'],
                    ],
                ];
            }, $incidents);

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features,
                'source' => basename($csvPath),
            ]);
        }

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
