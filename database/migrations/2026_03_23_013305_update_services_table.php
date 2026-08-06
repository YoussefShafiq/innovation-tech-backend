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
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (!Schema::hasColumn('services', 'tags')) {
                $table->string('tags')->nullable();
            }
            
            // Drop unwanted columns
            $toDrop = ['cover_photo', 'content1', 'image1', 'content2', 'image2', 'content3', 'image3'];
            foreach ($toDrop as $col) {
                if (Schema::hasColumn('services', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('cover_photo')->nullable()->after('slug');
            $table->longText('content1')->nullable();
            $table->string('image1')->nullable();
            $table->longText('content2')->nullable();
            $table->string('image2')->nullable();
            $table->longText('content3')->nullable();
            $table->string('image3')->nullable();
            $table->dropColumn(['icon', 'tags']);
        });
    }
};
