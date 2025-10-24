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
						$table->boolean('is_activated')->default(false);
						$table->integer('days_worked')->default(0);
						$table->boolean('is_serviced')->default(false);
						$table->text('warnings')->nullable();
						$table->text('errors')->nullable();
				});
		}

		public function down()
		{
				Schema::table('stations', function (Blueprint $table) {
						$table->dropColumn(['is_activated', 'days_worked', 'is_serviced', 'warnings', 'errors']);
				});
		}

};
