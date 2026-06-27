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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('team_id')->nullable();    // Tim yang mengerjakan (nullable = unassigned)
            $table->uuid('status_id');              // Kolom status Kanban
            $table->uuid('assigned_to')->nullable(); // User yang ditugaskan
            $table->uuid('created_by');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->date('due_date')->nullable();
            $table->integer('position')->default(0); // Urutan dalam kolom Kanban

            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('task_statuses')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['project_id', 'status_id', 'position']);
            $table->index(['team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
