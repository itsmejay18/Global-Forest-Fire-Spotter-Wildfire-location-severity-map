<?php

namespace Tests\Feature\Api;

use App\Models\WildfireIncident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WildfireIncidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_wildfire_incidents_as_geojson(): void
    {
        WildfireIncident::factory()->create([
            'name' => 'Test Fire',
            'latitude' => 10.5,
            'longitude' => 20.5,
            'burned_area' => 100.0,
            'fire_date' => '2023-05-15',
            'month' => 5,
            'year' => 2023,
            'severity' => 'High',
            'country' => 'TestLand',
            'duration' => 10,
        ]);

        $response = $this->getJson('/api/incidents');

        $response->assertStatus(200)
            ->assertJson([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [20.5, 10.5],
                        ],
                        'properties' => [
                            'name' => 'Test Fire',
                            'burned_area' => 100.0,
                            'fire_date' => '2023-05-15',
                            'severity' => 'High',
                            'country' => 'TestLand',
                            'month' => 5,
                            'year' => 2023,
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_filter_wildfire_incidents(): void
    {
        WildfireIncident::factory()->create(['month' => 5, 'year' => 2023, 'severity' => 'High']);
        WildfireIncident::factory()->create(['month' => 6, 'year' => 2023, 'severity' => 'Low']);
        WildfireIncident::factory()->create(['month' => 5, 'year' => 2022, 'severity' => 'Medium']);

        // Filter by month
        $response = $this->getJson('/api/incidents?month=5');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('features'));

        // Filter by year
        $response = $this->getJson('/api/incidents?year=2022');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('features'));

        // Filter by severity
        $response = $this->getJson('/api/incidents?severity=Low');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('features'));

        // Multiple filters
        $response = $this->getJson('/api/incidents?month=5&year=2023');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('features'));
    }
}
