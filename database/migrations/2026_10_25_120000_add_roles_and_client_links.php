<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['nurse', 'patient'])->default('nurse')->after('email');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('email')->nullable()->after('gender');
            $table->timestamp('patient_confirmed_at')->nullable()->after('risk_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'email', 'patient_confirmed_at']);
        });
    }
};
