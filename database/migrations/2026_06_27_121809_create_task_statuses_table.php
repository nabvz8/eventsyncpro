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
        Schema::create('task_statuses', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            } else {
                $table->uuid('id')->primary();
            }
            $table->uuid('project_id');
            $table->string('name', 50);
            $table->string('color_hex', 7)->default('#9E9E9E');
            $table->integer('position');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unique(['project_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
