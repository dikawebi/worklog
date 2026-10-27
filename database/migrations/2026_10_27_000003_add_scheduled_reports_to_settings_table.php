<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('scheduled_report_enabled')->default(false);
            $table->string('scheduled_report_frequency', 20)->default('weekly');
            $table->time('scheduled_report_time')->default('08:00:00');
            $table->timestamp('scheduled_report_last_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_report_enabled',
                'scheduled_report_frequency',
                'scheduled_report_time',
                'scheduled_report_last_sent_at',
            ]);
        });
    }
};
