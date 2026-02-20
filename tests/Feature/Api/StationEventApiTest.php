<?php

namespace Tests\Feature\Api;

use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationEventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_stored(): void
    {
        $station = Station::factory()->create(['code' => 'EVT01']);

        $response = $this->postJson('/work/api-events.php', [
            'station' => ['EVT01'],
            'event_type' => 'start',
            'washing_machine_number' => 1,
            'program_number' => 5,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'message' => 'Event stored.']);

        $this->assertDatabaseHas('station_logs', [
            'station_id' => $station->id,
            'event_type' => 'start',
            'washing_machine_number' => 1,
            'program_number' => 5,
        ]);
    }

    public function test_event_supports_alternative_event_field(): void
    {
        $station = Station::factory()->create(['code' => 'EVT02']);

        $response = $this->postJson('/work/api-events.php', [
            'station' => ['EVT02'],
            'event' => 'done',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('station_logs', [
            'station_id' => $station->id,
            'event_type' => 'done',
        ]);
    }

    public function test_event_supports_done_array_format(): void
    {
        $station = Station::factory()->create(['code' => 'EVT03']);

        $response = $this->postJson('/work/api-events.php', [
            'station' => ['EVT03'],
            'event_type' => 'done',
            'done' => [3, 7, 50.5],
            'sitr' => [1, 0, 1, 0, 1, 0],
            'deterg' => [0, 1, 0, 1, 0, 1, 0, 1],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('station_logs', [
            'station_id' => $station->id,
            'washing_machine_number' => 3,
            'program_number' => 7,
        ]);
    }

    public function test_event_returns_404_for_unknown_station(): void
    {
        $response = $this->postJson('/work/api-events.php', [
            'station' => ['NOPE'],
            'event_type' => 'test',
        ]);

        $response->assertStatus(404);
    }

    public function test_event_with_custom_occurred_at(): void
    {
        $station = Station::factory()->create(['code' => 'EVT04']);

        $response = $this->postJson('/work/api-events.php', [
            'station' => ['EVT04'],
            'event_type' => 'test',
            'occurred_at' => '2025-06-15 14:30:00',
        ]);

        $response->assertOk();

        $log = $station->logs()->first();
        $this->assertEquals('2025-06-15', $log->created_at->toDateString());
    }

    public function test_event_with_alternate_date_format(): void
    {
        $station = Station::factory()->create(['code' => 'EVT05']);

        $response = $this->postJson('/work/api-events.php', [
            'station' => ['EVT05'],
            'event_type' => 'test',
            'date' => '2025-06-15-14-30-00',
        ]);

        $response->assertOk();

        $log = $station->logs()->first();
        $this->assertEquals('2025-06-15', $log->created_at->toDateString());
    }

    public function test_event_stores_machine_and_detergent_signals(): void
    {
        $station = Station::factory()->create(['code' => 'EVT06']);

        $this->postJson('/work/api-events.php', [
            'station' => ['EVT06'],
            'event_type' => 'running',
            'machine_signals' => [1, 0, 1, 0, 1, 0],
            'detergent_signals' => [0, 1, 0, 1],
        ]);

        $log = $station->logs()->first();
        $this->assertEquals([1, 0, 1, 0, 1, 0], $log->machine_signals);
        $this->assertEquals([0, 1, 0, 1], $log->detergent_signals);
    }

    public function test_event_rejects_unsupported_endpoint(): void
    {
        $response = $this->postJson('/work/api-events.php?endpoint=wrong', [
            'station' => ['X'],
            'event_type' => 'test',
        ]);

        $response->assertStatus(400);
    }
}
