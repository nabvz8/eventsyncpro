<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to onboard a user to a workspace.
     */
    protected function onboardUserToWorkspace(User $user, Workspace $workspace, string $role = 'owner'): void
    {
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role_workspace' => $role,
            'status_invitation' => 'accepted',
        ]);
    }

    /**
     * Project Leader terotorisasi dapat membuat tim baru di bawah proyek.
     */
    public function test_authorized_project_leader_can_create_team(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Project',
            'created_by' => $owner->id,
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role_project' => 'leader',
        ]);

        $response = $this->actingAs($owner)
            ->post("/projects/{$project->id}/teams", [
                'name' => 'Frontend Developers',
                'description' => 'Team responsible for frontend client work',
            ]);

        $response->assertRedirect();

        // Verifikasi tim berhasil dibuat
        $team = Team::where('project_id', $project->id)->first();
        $this->assertNotNull($team);
        $this->assertEquals('Frontend Developers', $team->name);
    }

    /**
     * Pengguna yang bukan Project Leader/Co-Leader dilarang membuat tim baru.
     */
    public function test_non_project_leader_cannot_create_team(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $member = User::factory()->create();
        $this->onboardUserToWorkspace($member, $workspace, 'member');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Project',
            'created_by' => $owner->id,
        ]);

        // member ini bukan project leader
        $response = $this->actingAs($member)
            ->post("/projects/{$project->id}/teams", [
                'name' => 'Hacker Team',
                'description' => 'Should fail',
            ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Team::count());
    }

    /**
     * Project Leader dapat mendaftarkan anggota workspace ke dalam tim proyek.
     */
    public function test_project_leader_can_add_member_to_team(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $member = User::factory()->create();
        $this->onboardUserToWorkspace($member, $workspace, 'member');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Project',
            'created_by' => $owner->id,
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role_project' => 'leader',
        ]);

        $team = Team::create([
            'project_id' => $project->id,
            'name' => 'Designers',
        ]);

        $response = $this->actingAs($owner)
            ->post("/teams/{$team->id}/members", [
                'user_id' => $member->id,
                'role' => 'member',
            ]);

        $response->assertRedirect();

        $this->assertTrue(
            TeamMember::where('team_id', $team->id)
                ->where('user_id', $member->id)
                ->where('role_team', 'member')
                ->exists()
        );
    }

    /**
     * Pengguna biasa di luar tim dan kepemimpinan proyek dilarang mendaftarkan anggota ke dalam tim.
     */
    public function test_non_team_or_project_leader_cannot_add_member_to_team(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $member1 = User::factory()->create();
        $this->onboardUserToWorkspace($member1, $workspace, 'member');

        $member2 = User::factory()->create();
        $this->onboardUserToWorkspace($member2, $workspace, 'member');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Project',
            'created_by' => $owner->id,
        ]);

        $team = Team::create([
            'project_id' => $project->id,
            'name' => 'QA Testers',
        ]);

        // member1 bukan leader tim atau leader proyek
        $response = $this->actingAs($member1)
            ->post("/teams/{$team->id}/members", [
                'user_id' => $member2->id,
                'role' => 'member',
            ]);

        $response->assertStatus(403);
    }
}
