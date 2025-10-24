<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ManualFeed;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
		{
			Schema::create('manual_feeds', function (Blueprint $table) {
					$table->id();
					$table->foreignId('station_id')->constrained()->cascadeOnDelete();
					$table->string('detergent');
					$table->decimal('ml', 8, 2);
					$table->string('washing_machine');
					$table->timestamps();
			});
		}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_feeds');
				ManualFeed::create([
						'station_id' => $this->record->id,
						'detergent' => $this->detergent,
						'ml' => $this->ml,
						'washing_machine' => $this->washing_machine,
				]);
    }
};
