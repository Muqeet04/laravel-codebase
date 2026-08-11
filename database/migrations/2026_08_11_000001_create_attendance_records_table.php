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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('person_name');
            $table->string('event_type', 50); // IN, OUT
            $table->dateTime('timestamp');
            $table->date('date');
            $table->float('confidence')->nullable();
            $table->string('snapshot_path', 500)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('person_name');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
