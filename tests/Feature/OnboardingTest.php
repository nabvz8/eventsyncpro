<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User yang belum onboarded harus dialihkan ke halaman onboarding.
     */
    public function test_unonboarded_user_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/onboarding');
    }

    /**
     * User yang sudah onboarded harus dialihkan ke dashboard jika mencoba mengakses onboarding.
     */
    public function test_onboarded_user_is_redirected_to_dashboard_from_onboarding(): void
    {
        $user = User::factory()->create();

        // Buat workspace agar user terhitung onboarded
        $workspace = Workspace::create([
            'name' => 'Existing Workspace',
            'owner_id' => $user->id,
        ]);

        \App\Models\WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role_workspace' => 'owner',
            'status_invitation' => 'accepted',
        ]);

        $response = $this->actingAs($user)->get('/onboarding');

        $response->assertRedirect('/dashboard');
    }

    /**
     * User dapat melengkapi setup onboarding.
     */
    public function test_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Old Name',
        ]);

        $response = $this->actingAs($user)->post('/onboarding', [
            'full_name' => 'New Full Name',
            'workspace_name' => 'Awesome Workspace',
            'workspace_description' => 'Workspace description text',
            'create_default_project' => true,
            'project_name' => 'My First Project',
            'project_description' => 'Project description text',
        ]);

        $response->assertRedirect('/dashboard');

        // Verifikasi profile user terupdate
        $this->assertEquals('New Full Name', $user->fresh()->full_name);

        // Verifikasi workspace terbuat
        $workspace = Workspace::where('owner_id', $user->id)->first();
        $this->assertNotNull($workspace);
        $this->assertEquals('Awesome Workspace', $workspace->name);

        // Verifikasi user masuk ke workspace_members sebagai owner
        $this->assertTrue($workspace->members()->where('user_id', $user->id)->where('role_workspace', 'owner')->exists());

        // Verifikasi proyek default terbuat
        $project = Project::where('workspace_id', $workspace->id)->first();
        $this->assertNotNull($project);
        $this->assertEquals('My First Project', $project->name);
        $this->assertEquals($user->id, $project->created_by);

        // Verifikasi status Kanban bawaan proyek terbuat
        $this->assertEquals(3, TaskStatus::where('project_id', $project->id)->count());
        $this->assertTrue(TaskStatus::where('project_id', $project->id)->where('name', 'To Do')->exists());
        $this->assertTrue(TaskStatus::where('project_id', $project->id)->where('name', 'In Progress')->exists());
        $this->assertTrue(TaskStatus::where('project_id', $project->id)->where('name', 'Done')->exists());
    }
}
