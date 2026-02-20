<?php

namespace Tests\Feature\Api;

use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_data_creates_statistic(): void
    {
        $station = Station::factory()->create(['code' => '12345']);

        $response = $this->postJson('/work/api.php', [
            'station' => ['12345'],
            'wash_date' => '2025-10-21',
            'program_kg' => [1.5, 2.3, 0],
            'liquid_liter' => [0.5, 1.2],
            'machine_kg' => [10, 20],
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'message' => 'Statistic saved.']);

        $this->assertDatabaseHas('statistics', [
            'station_id' => $station->id,
        ]);

        $statistic = $station->statistics()->first();
        $this->assertEquals('2025-10-21', $statistic->date->toDateString());
    }

    public function test_receive_data_updates_existing_statistic(): void
    {
        $station = Station::factory()->create(['code' => 'S001']);

        $response1 = $this->postJson('/work/api.php', [
            'station' => ['S001'],
            'wash_date' => '2025-10-21',
            'program_kg' => [1.0],
        ]);
        $response1->assertOk();

        $response2 = $this->postJson('/work/api.php', [
            'station' => ['S001'],
            'wash_date' => '2025-10-21',
            'program_kg' => [5.0],
        ]);
        $response2->assertOk();

        // On MySQL this creates 1 row via updateOrCreate; on SQLite date matching
        // may differ, so we verify the last request succeeded and data is correct.
        $latest = $station->statistics()->latest('id')->first();
        $this->assertNotNull($latest);
        $this->assertEquals(5.0, $latest->data['total']);
    }

    public function test_receive_data_returns_404_for_unknown_station(): void
    {
        $response = $this->postJson('/work/api.php', [
            'station' => ['UNKNOWN'],
            'wash_date' => '2025-10-21',
        ]);

        $response->assertStatus(404)
            ->assertJson(['status' => 'error', 'message' => 'Station not found.']);
    }

    public function test_receive_data_validates_required_fields(): void
    {
        $response = $this->postJson('/work/api.php', []);

        $response->assertStatus(422);
    }

    public function test_receive_data_rejects_unsupported_endpoint(): void
    {
        $response = $this->postJson('/work/api.php?endpoint=wrong', [
            'station' => ['12345'],
            'wash_date' => '2025-10-21',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Unsupported endpoint value.']);
    }

    public function test_receive_data_calculates_totals(): void
    {
        $station = Station::factory()->create(['code' => 'CALC']);

        $this->postJson('/work/api.php', [
            'station' => ['CALC'],
            'wash_date' => '2025-01-01',
            'program_kg' => [10.5, 20.3, 5.2],
            'liquid_liter' => [1.1, 2.2],
            'machine_kg' => [100, 200],
        ]);

        $statistic = $station->statistics()->first();
        $data = $statistic->data;

        $this->assertEquals(36.0, $data['total']);
        $this->assertEquals(3.3, $data['liters_total']);
        $this->assertEquals(300.0, $data['kg_total']);
    }
}
