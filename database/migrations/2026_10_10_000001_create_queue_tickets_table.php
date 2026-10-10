<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date');
            $table->unsignedInteger('number');
            $table->string('access_token', 64)->unique();
            $table->string('device_token', 64)->nullable()->index();
            $table->string('source', 10)->default('self');
            $table->string('status', 20)->default('waiting');
            $table->string('patient_name')->nullable();
            $table->json('answers')->nullable();
            $table->string('suggested_priority', 20)->nullable();
            $table->string('priority', 20)->nullable();
            $table->string('priority_note')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            # numbers restart every day
            $table->unique(['queue_date', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
