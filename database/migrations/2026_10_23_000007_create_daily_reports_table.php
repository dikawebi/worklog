<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('report_date');
            $table->text('notes')->nullable();
            $table->timestamp('first_submitted_at')->nullable();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedTinyInteger('active')
                ->nullable()
                ->storedAs('IF(deleted_at IS NULL, 1, NULL)');

            $table->unique(['user_id', 'report_date', 'active']);
            $table->index(['report_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
