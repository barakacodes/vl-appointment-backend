<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->enum('gender', ['M', 'F']);
            $table->float('distance_km');
            $table->integer('previous_missed_count')->default(0);
            $table->float('avg_delay_days')->default(0);
            $table->date('next_appointment_date')->nullable();
            $table->float('risk_score')->nullable();
            $table->string('risk_tier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
