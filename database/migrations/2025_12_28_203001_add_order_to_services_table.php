<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('show_on_home');
        });

        // Set default order for existing services
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            DB::table('services')
                ->where('id', $service->id)
                ->update(['order' => $service->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
