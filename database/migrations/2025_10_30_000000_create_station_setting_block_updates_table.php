<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_setting_block_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('block_number');
            $table->string('changed_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['station_id', 'block_number']);
            $table->index(['station_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_setting_block_updates');
    }
};
