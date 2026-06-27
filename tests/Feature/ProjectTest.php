<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
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
     * Anggota terotorisasi (misalnya Owner Workspace) dapat membuat proyek baru.
     */
    public function test_authorized_workspace_owner_can_create_project(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'My Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        // Set session active workspace
        $response = $this->actingAs($owner)
            ->withSession(['active_workspace_id' => $workspace->id])
            ->post('/projects', [
                'name' => 'New Awesome Project',
                'description' => 'Project description',
            ]);

        $response->assertRedirect();

        // Verifikasi proyek tersimpan
        $project = Project::where('workspace_id', $workspace->id)->first();
        $this->assertNotNull($project);
        $this->assertEquals('New Awesome Project', $project->name);

        // Verifikasi creator ditunjuk sebagai Project Leader
        $this->assertTrue(
            ProjectMember::where('project_id', $project->id)
                ->where('user_id', $owner->id)
                ->where('role_project', 'leader')
                ->exists()
        );

        // Verifikasi default Kanban status terbuat
        $this->assertEquals(3, TaskStatus::where('project_id', $project->id)->count());
    }

    /**
     * User yang bukan anggota workspace dilarang membuat proyek di workspace tersebut.
     */
    public function test_non_workspace_member_cannot_create_project_in_workspace(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Owner Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $otherUser = User::factory()->create();
        $otherWorkspace = Workspace::create([
            'name' => 'Other Workspace',
            'owner_id' => $otherUser->id,
        ]);
        $this->onboardUserToWorkspace($otherUser, $otherWorkspace, 'owner');

        $response = $this->actingAs($otherUser)
            ->withSession(['active_workspace_id' => $workspace->id])
            ->post('/projects', [
                'name' => 'Intruder Project',
                'description' => 'Hack description',
            ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Project::count());
    }

    /**
     * Anggota terotorisasi dapat melihat detail proyek.
     */
    public function test_authorized_member_can_view_project(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'My Project',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->get("/projects/{$project->id}");

        $response->assertOk();
    }

    /**
     * User di luar workspace tidak dapat melihat detail proyek.
     */
    public function test_non_workspace_member_cannot_view_project(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'My Project',
            'created_by' => $owner->id,
        ]);

        $otherUser = User::factory()->create();
        $otherWorkspace = Workspace::create([
            'name' => 'Other Workspace',
            'owner_id' => $otherUser->id,
        ]);
        $this->onboardUserToWorkspace($otherUser, $otherWorkspace, 'owner');

        $response = $this->actingAs($otherUser)
            ->get("/projects/{$project->id}");

        $response->assertStatus(403);
    }

    /**
     * Project Leader dapat mendaftarkan anggota workspace ke proyek dengan peran terpilih.
     */
    public function test_project_leader_can_add_workspace_member_to_project(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Workspace',
            'owner_id' => $owner->id,
        ]);
        $this->onboardUserToWorkspace($owner, $workspace, 'owner');

        // Buat member workspace baru
        $newMember = User::factory()->create();
        $this->onboardUserToWorkspace($newMember, $workspace, 'member');

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'My Project',
            'created_by' => $owner->id,
        ]);
        // Tunjuk owner sebagai leader proyek
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role_project' => 'leader',
        ]);

        $response = $this->actingAs($owner)
            ->post("/projects/{$project->id}/members", [
                'user_id' => $newMember->id,
                'role' => 'co_leader',
            ]);

        $response->assertRedirect();

        $this->assertTrue(
            ProjectMember::where('project_id', $project->id)
                ->where('user_id', $newMember->id)
                ->where('role_project', 'co_leader')
                ->exists()
        );
    }

    /**
     * Pengguna yang bukan Project Leader/Co-Leader atau Workspace Owner dilarang menambah anggota proyek.
     */
    public function test_non_project_leader_cannot_add_member(): void
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
            'name' => 'My Project',
            'created_by' => $owner->id,
        ]);

        // Member1 bukan leader/co_leader
        $response = $this->actingAs($member1)
            ->post("/projects/{$project->id}/members", [
                'user_id' => $member2->id,
                'role' => 'co_leader',
            ]);

        $response->assertStatus(403);
    }
}
