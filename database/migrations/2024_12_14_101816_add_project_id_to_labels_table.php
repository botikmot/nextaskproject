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
        Schema::table('labels', function (Blueprint $table) {
            $table->uuid('project_id')->after('id'); // Add the project_id column
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade'); // Add foreign key    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropForeign(['project_id']); // Drop the foreign key
            $table->dropColumn('project_id'); // Drop the project_id column
        });
    }
};
