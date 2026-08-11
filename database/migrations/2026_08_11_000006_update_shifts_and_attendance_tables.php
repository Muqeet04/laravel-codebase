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
        // Add 'date' column to shifts for temporary shifts
        if (!Schema::hasColumn('shifts', 'date')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->string('date', 10)->nullable()->after('days');
            });
        }

        // Drop old unique constraint on person_name if it exists, add composite unique
        // Note: Laravel may not have the old unique constraint if created fresh via our migration
        try {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropUnique(['person_name']);
            });
        } catch (\Exception $e) {
            // Constraint may not exist
        }

        try {
            Schema::table('shifts', function (Blueprint $table) {
                $table->unique(['person_name', 'date'], 'uq_person_date');
            });
        } catch (\Exception $e) {
            // Composite unique may already exist
        }

        // Add 'late_minutes' column to attendance_records
        if (!Schema::hasColumn('attendance_records', 'late_minutes')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->integer('late_minutes')->default(0)->after('confidence');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shifts', 'date')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }

        if (Schema::hasColumn('attendance_records', 'late_minutes')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropColumn('late_minutes');
            });
        }
    }
};
