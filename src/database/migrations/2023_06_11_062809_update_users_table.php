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
        Schema::table('users', function (Blueprint $table) {
            $table->string('global_name')->nullable()->after('username');
            $table->string('discriminator')->nullable()->change();
            $table->string('banner')->nullable()->after('verified');
            $table->string('banner_color')->nullable()->after('banner');
            $table->string('accent_color')->nullable()->after('banner_color');
            $table->string('premium_type')->nullable()->after('mfa_enabled');
            $table->string('public_flags')->nullable()->after('premium_type');
            $table->boolean('verified')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('global_name');
            $table->string('discriminator')->change();
            $table->dropColumn('banner');
            $table->dropColumn('banner_color');
            $table->dropColumn('accent_color');
            $table->dropColumn('premium_type');
            $table->dropColumn('public_flags');
            $table->boolean('verified')->change();
        });
    }
};
