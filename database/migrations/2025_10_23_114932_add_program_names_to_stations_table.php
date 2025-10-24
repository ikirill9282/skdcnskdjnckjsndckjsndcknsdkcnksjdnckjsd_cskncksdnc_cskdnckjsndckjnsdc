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
				Schema::table('stations', function (Blueprint $table) {
						$table->json('program_names')->nullable();
				});
		}

		public function down()
		{
				Schema::table('stations', function (Blueprint $table) {
						$table->dropColumn('program_names');
				});
		}

};
