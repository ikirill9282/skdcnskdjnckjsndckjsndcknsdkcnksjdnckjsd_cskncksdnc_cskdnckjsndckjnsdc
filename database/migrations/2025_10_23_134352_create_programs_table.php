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
				Schema::create('programs', function (Blueprint $table) {
						$table->id();
						$table->foreignId('station_id')->constrained()->cascadeOnDelete();
						$table->integer('program_number'); // от 1 до 19
						$table->string('name');
						$table->timestamps();
						
						$table->unique(['station_id', 'program_number']);
				});
		}

		public function down()
		{
				Schema::dropIfExists('programs');
		}

};
