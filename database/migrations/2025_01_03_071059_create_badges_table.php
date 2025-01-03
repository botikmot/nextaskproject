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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Badge name
            $table->string('description')->nullable(); // Description
            $table->string('icon')->nullable(); // Path to badge icon
            $table->string('category')->nullable(); // Badge category (e.g., tasks, projects)
            $table->integer('threshold')->nullable(); // Criteria for earning (e.g., 10 tasks)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
