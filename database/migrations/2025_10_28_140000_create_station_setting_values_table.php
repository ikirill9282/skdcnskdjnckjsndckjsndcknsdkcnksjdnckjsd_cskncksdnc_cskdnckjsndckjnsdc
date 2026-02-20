<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_setting_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('block_number');
            $table->unsignedTinyInteger('setting_index');
            $table->text('value');
            $table->timestamps();

            $table->unique(
                ['station_id', 'block_number', 'setting_index'],
                'ssv_station_block_setting_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_setting_values');
    }
};
