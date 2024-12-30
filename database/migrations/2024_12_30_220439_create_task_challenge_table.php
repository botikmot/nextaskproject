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
        Schema::create('task_challenge', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id'); // Use `uuid` if tasks use UUIDs
            $table->uuid('challenge_id'); // Use `uuid` if challenges use UUIDs
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('challenge_id')->references('id')->on('challenges')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_challenge');
    }
};
