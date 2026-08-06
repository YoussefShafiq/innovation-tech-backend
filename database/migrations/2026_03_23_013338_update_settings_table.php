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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('story_title')->nullable();
            $table->string('story_subtitle')->nullable();
            $table->text('story_description')->nullable();
            $table->text('story_bullets')->nullable(); // JSON
            $table->dropColumn(['image', 'portfolio']);
            if (Schema::hasColumn('settings', 'portfolio_file')) {
                $table->dropColumn('portfolio_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['story_title', 'story_subtitle', 'story_description', 'story_bullets']);
        });
    }
};
