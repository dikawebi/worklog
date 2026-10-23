<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('app_name', 100)->default('IT Worklog');
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->json('workdays');
            $table->time('reminder_time')->default('17:00:00');
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
