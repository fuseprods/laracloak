<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->integer('refresh_rate')->nullable()->after('type'); // Dashboards
            $table->text('success_message')->nullable()->after('refresh_rate'); // Forms
            $table->string('redirect_url')->nullable()->after('success_message'); // Forms
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['refresh_rate', 'success_message', 'redirect_url']);
        });
    }
};
