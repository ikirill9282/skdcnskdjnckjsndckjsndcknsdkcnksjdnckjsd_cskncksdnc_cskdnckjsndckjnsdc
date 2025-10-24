<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
		{
				Schema::create('station_logs', function (Blueprint $table) {
						$table->id();
						$table->foreignId('station_id')->constrained()->cascadeOnDelete();
						$table->string('event_type'); // последнее средство, подача средства и т.д.
						$table->integer('washing_machine_number')->nullable();
						$table->integer('program_number')->nullable();
						$table->decimal('white_loading', 8, 2)->nullable();
						$table->integer('signal_1')->nullable();
						$table->integer('signal_2')->nullable();
						$table->json('machine_signals')->nullable(); // сигналы для всех машин
						$table->json('detergent_signals')->nullable(); // моющие средства
						$table->text('comment')->nullable();
						$table->timestamps();
				});
		}

		public function down()
		{
				Schema::dropIfExists('station_logs');
		}

};
