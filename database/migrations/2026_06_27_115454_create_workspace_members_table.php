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
        Schema::create('workspace_members', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            } else {
                $table->uuid('id')->primary();
            }
            $table->uuid('workspace_id');
            $table->uuid('user_id');
            $table->string('role_workspace', 20)->default('member'); // 'owner' | 'admin' | 'member'
            $table->string('status_invitation', 20)->default('accepted'); // 'pending' | 'accepted' | 'expired'
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['workspace_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
