<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
					$table->string('current_status')->nullable();
					$table->string('current_detergent')->nullable();
					$table->decimal('current_volume', 8, 2)->nullable();
					$table->string('current_washing_machine')->nullable();
					$table->integer('current_process_completion')->default(0);
   			});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
        	$table->dropColumn([
            'current_status', 
            'current_detergent', 
            'current_volume', 
            'current_washing_machine', 
            'current_process_completion'
       		]);
    		});
    }
};
