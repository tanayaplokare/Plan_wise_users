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
        Schema::table('filter_uploads', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('id');
            $table->json('keywords')->nullable()->after('filtered_column');
        
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_uploads', function (Blueprint $table) {
            $table->dropColumn('original_filename');
            $table->dropColumn('keywords');
        });
    }
};
