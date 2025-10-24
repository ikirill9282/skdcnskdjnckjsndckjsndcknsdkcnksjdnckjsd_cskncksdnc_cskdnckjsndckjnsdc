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
						// Удаляем старые boolean поля, если они есть
						if (Schema::hasColumn('stations', 'is_activated')) {
								$table->dropColumn('is_activated');
						}
						if (Schema::hasColumn('stations', 'is_serviced')) {
								$table->dropColumn('is_serviced');
						}
						
						// Добавляем новые date поля
						$table->date('activation_date')->nullable();
						$table->date('service_date')->nullable();
				});
		}

		public function down()
		{
				Schema::table('stations', function (Blueprint $table) {
						$table->dropColumn(['activation_date', 'service_date']);
						$table->boolean('is_activated')->default(false);
						$table->boolean('is_serviced')->default(false);
				});
		}

};
