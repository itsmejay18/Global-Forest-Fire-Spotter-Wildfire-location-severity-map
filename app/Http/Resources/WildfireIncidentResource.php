<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WildfireIncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    (float) $this->longitude,
                    (float) $this->latitude,
                ],
            ],
            'properties' => [
                'id' => $this->id,
                'name' => $this->name,
                'burned_area' => (float) $this->burned_area,
                'fire_date' => $this->fire_date->format('Y-m-d'),
                'severity' => $this->severity,
                'country' => $this->country,
                'duration' => $this->duration,
                'month' => $this->month,
                'year' => $this->year,
            ],
        ];
    }
}
