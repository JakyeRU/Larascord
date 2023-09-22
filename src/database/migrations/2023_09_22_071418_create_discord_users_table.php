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
        Schema::create('discord_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('discriminator');
            $table->string('global_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('banner')->nullable();
            $table->integer('accent_color')->nullable();
            $table->integer('public_flags')->nullable();
            $table->integer('flags')->nullable();
            $table->string('locale');
            $table->integer('premium_type')->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_users');
    }
};
