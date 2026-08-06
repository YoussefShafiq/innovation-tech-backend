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
        Schema::rename('feedback', 'contacts');
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('subject')->nullable()->after('email');
            $table->dropColumn('title');
            $table->dropColumn('image');
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->string('image')->nullable()->after('message');
            $table->dropColumn(['email', 'subject']);
        });
        Schema::rename('contacts', 'feedback');
    }
};
