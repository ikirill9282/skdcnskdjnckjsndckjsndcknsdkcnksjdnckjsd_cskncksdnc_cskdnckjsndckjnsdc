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
				Schema::create('statistics', function (Blueprint $table) {
						$table->id();
						$table->foreignId('station_id')->constrained()->cascadeOnDelete();
						$table->date('date');
						$table->json('data'); // Данные по всем столбцам
						$table->timestamps();
				});
		}

		public function down()
		{
				Schema::dropIfExists('statistics');
		}

};
