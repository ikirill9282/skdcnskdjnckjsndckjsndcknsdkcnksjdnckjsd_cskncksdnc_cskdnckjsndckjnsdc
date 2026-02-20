<?php

namespace Tests\Feature\Api;

use App\Models\Station;
use App\Models\StationSettingBlockUpdate;
use App\Models\StationSettingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_standard_payload_updates_station(): void
    {
        $station = Station::factory()->create(['code' => 'SET01']);

        $response = $this->postJson('/work/api-setting.php', [
            'station' => ['SET01'],
            'current_status' => 'running',
            'current_detergent' => 'Detergent A',
            'current_volume' => 15.5,
            'machines' => [['name' => 'M1']],
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'message' => 'Settings updated.']);

        $station->refresh();
        $this->assertEquals('running', $station->current_status);
        $this->assertEquals('Detergent A', $station->current_detergent);
        $this->assertEquals(15.5, $station->current_volume);
    }

    public function test_store_compact_payload_creates_setting_values(): void
    {
        $station = Station::factory()->create(['code' => '999']);

        $response = $this->postJson('/work/api-setting.php', [
            '999', 306, 'idle', 'det1', 10, 'machine1', 50,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'message' => 'Setting block stored.']);

        $this->assertDatabaseHas('station_setting_values', [
            'station_id' => $station->id,
            'block_number' => 306,
            'setting_index' => 1,
            'value' => 'idle',
        ]);
    }

    public function test_store_compact_payload_rejects_too_small(): void
    {
        $response = $this->postJson('/work/api-setting.php', ['999', 306]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid payload size.']);
    }

    public function test_store_returns_404_for_unknown_station(): void
    {
        $response = $this->postJson('/work/api-setting.php', [
            'station' => ['NOPE'],
        ]);

        $response->assertStatus(404);
    }

    public function test_show_returns_pending_block_update(): void
    {
        $station = Station::factory()->create(['code' => '777']);

        StationSettingValue::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'setting_index' => 1,
            'value' => 'running',
        ]);
        StationSettingValue::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'setting_index' => 2,
            'value' => 'det1',
        ]);

        StationSettingBlockUpdate::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'changed_by' => 'admin',
            'sent_at' => null,
        ]);

        $response = $this->getJson('/work/api-setting.php?id=777');

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals(777, $data[0]);
        $this->assertEquals(306, $data[1]);
        $this->assertEquals('running', $data[2]);
        $this->assertEquals('det1', $data[3]);
    }

    public function test_show_returns_empty_when_no_pending_updates(): void
    {
        Station::factory()->create(['code' => '888']);

        $response = $this->getJson('/work/api-setting.php?id=888');

        $response->assertOk()->assertJson([]);
    }

    public function test_show_marks_block_as_sent(): void
    {
        $station = Station::factory()->create(['code' => '666']);

        StationSettingValue::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'setting_index' => 1,
            'value' => 'test',
        ]);

        StationSettingBlockUpdate::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'changed_by' => 'admin',
            'sent_at' => null,
        ]);

        $this->getJson('/work/api-setting.php?id=666');

        $update = StationSettingBlockUpdate::where('station_id', $station->id)
            ->where('block_number', 306)
            ->first();

        $this->assertNotNull($update->sent_at);
    }

    public function test_show_requires_station_id(): void
    {
        $response = $this->getJson('/work/api-setting.php');

        $response->assertStatus(422)
            ->assertJson(['message' => 'Station identifier is required.']);
    }

    public function test_show_returns_404_for_unknown_station(): void
    {
        $response = $this->getJson('/work/api-setting.php?id=NOPE');

        $response->assertStatus(404);
    }

    public function test_store_rejects_unsupported_endpoint(): void
    {
        $response = $this->postJson('/work/api-setting.php?endpoint=wrong', [
            'station' => ['X'],
        ]);

        $response->assertStatus(400);
    }

    public function test_show_rejects_unsupported_endpoint(): void
    {
        $response = $this->getJson('/work/api-setting.php?endpoint=wrong');

        $response->assertStatus(400);
    }

    public function test_store_updates_activation_and_service_dates(): void
    {
        $station = Station::factory()->create(['code' => 'DATE1']);

        $this->postJson('/work/api-setting.php', [
            'station' => ['DATE1'],
            'activation_date' => '2025-01-15',
            'service_date' => '2025-06-15',
        ]);

        $station->refresh();
        $this->assertEquals('2025-01-15', $station->activation_date->toDateString());
        $this->assertEquals('2025-06-15', $station->service_date->toDateString());
    }
}
