<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('machine_number'); // номер машины (1, 2, 3, 4...)
            $table->integer('program_1')->default(0);
            $table->integer('program_2')->default(0);
            $table->integer('program_3')->default(0);
            $table->integer('program_4')->default(0);
            $table->integer('program_5')->default(0);
            $table->integer('program_6')->default(0);
            $table->integer('program_7')->default(0);
            $table->integer('program_8')->default(0);
            $table->integer('program_9')->default(0);
            $table->integer('program_10')->default(0);
            $table->integer('program_11')->default(0);
            $table->integer('program_12')->default(0);
            $table->integer('program_13')->default(0);
            $table->integer('program_14')->default(0);
            $table->integer('program_15')->default(0);
            $table->integer('program_16')->default(0);
            $table->integer('program_17')->default(0);
            $table->integer('program_18')->default(0);
            $table->integer('program_19')->default(0);
            $table->integer('total')->default(0);
            $table->timestamps();
            
            $table->unique(['station_id', 'date', 'machine_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_statistics');
    }
};
