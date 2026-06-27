<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat setup data dasar: workspace, project, task status Kanban.
     */
    private function createProjectSetup(): array
    {
        $owner = User::create([
            'full_name' => 'Owner User',
            'username' => 'owner_user_task',
            'email' => 'owner_task@test.com',
            'password_hash' => bcrypt('password'),
            'role_global' => 'user',
        ]);

        $member = User::create([
            'full_name' => 'Member User',
            'username' => 'member_user_task',
            'email' => 'member_task@test.com',
            'password_hash' => bcrypt('password'),
            'role_global' => 'user',
        ]);

        $workspace = Workspace::create([
            'name' => 'Test Workspace',
            'owner_id' => $owner->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role_workspace' => 'owner',
            'status_invitation' => 'accepted',
            'joined_at' => now(),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role_workspace' => 'member',
            'status_invitation' => 'accepted',
            'joined_at' => now(),
        ]);

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Test Project',
            'created_by' => $owner->id,
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role_project' => 'leader',
        ]);

        $todoStatus = TaskStatus::create([
            'project_id' => $project->id,
            'name' => 'To Do',
            'color_hex' => '#9E9E9E',
            'position' => 1,
        ]);

        $inProgressStatus = TaskStatus::create([
            'project_id' => $project->id,
            'name' => 'In Progress',
            'color_hex' => '#2196F3',
            'position' => 2,
        ]);

        return compact('owner', 'member', 'workspace', 'project', 'todoStatus', 'inProgressStatus');
    }

    /**
     * Project leader dapat membuat task di proyeknya.
     */
    public function test_project_leader_can_create_task(): void
    {
        $data = $this->createProjectSetup();

        $response = $this->actingAs($data['owner'])
            ->post("/projects/{$data['project']->id}/tasks", [
                'title' => 'Setup Database',
                'priority' => 'high',
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, Task::where('project_id', $data['project']->id)->count());
    }

    /**
     * Anggota workspace yang bukan project member juga bisa membuat task (akses workspace member).
     */
    public function test_workspace_member_can_create_task(): void
    {
        $data = $this->createProjectSetup();

        $response = $this->actingAs($data['member'])
            ->post("/projects/{$data['project']->id}/tasks", [
                'title' => 'Design UI',
                'priority' => 'medium',
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, Task::where('project_id', $data['project']->id)->count());
    }

    /**
     * Anggota tim biasa hanya dapat membuat task di timnya sendiri (bukan tim lain).
     */
    public function test_team_member_cannot_create_task_for_other_team(): void
    {
        $data = $this->createProjectSetup();

        // Buat dua tim
        $teamA = Team::create(['project_id' => $data['project']->id, 'name' => 'Team A']);
        $teamB = Team::create(['project_id' => $data['project']->id, 'name' => 'Team B']);

        // Daftarkan member ke Team A
        TeamMember::create([
            'team_id' => $teamA->id,
            'user_id' => $data['member']->id,
            'role_team' => 'member',
        ]);

        // Member mencoba membuat task untuk Team B (tim lain)
        $response = $this->actingAs($data['member'])
            ->post("/projects/{$data['project']->id}/tasks", [
                'title' => 'Forbidden Task',
                'team_id' => $teamB->id,
                'priority' => 'low',
            ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Task::count());
    }

    /**
     * Project leader dapat membuat task untuk tim manapun (tidak dibatasi).
     */
    public function test_project_leader_can_create_task_for_any_team(): void
    {
        $data = $this->createProjectSetup();

        $teamA = Team::create(['project_id' => $data['project']->id, 'name' => 'Team A']);
        $teamB = Team::create(['project_id' => $data['project']->id, 'name' => 'Team B']);

        // Leader membuat task untuk Team B tanpa menjadi anggota tim tersebut
        $response = $this->actingAs($data['owner'])
            ->post("/projects/{$data['project']->id}/tasks", [
                'title' => 'Task for Team B',
                'team_id' => $teamB->id,
                'priority' => 'medium',
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, Task::where('team_id', $teamB->id)->count());
    }

    /**
     * Workspace member dapat memindahkan task (update status) di board.
     */
    public function test_workspace_member_can_move_task(): void
    {
        $data = $this->createProjectSetup();

        $task = Task::create([
            'project_id' => $data['project']->id,
            'status_id' => $data['todoStatus']->id,
            'created_by' => $data['owner']->id,
            'title' => 'Task to Move',
            'priority' => 'medium',
            'position' => 0,
        ]);

        $response = $this->actingAs($data['member'])
            ->putJson("/tasks/{$task->id}", [
                'status_id' => $data['inProgressStatus']->id,
                'position' => 0,
            ]);

        $response->assertOk();
        $this->assertEquals($data['inProgressStatus']->id, Task::find($task->id)->status_id);
    }

    /**
     * Team member tidak bisa memindahkan task milik tim lain.
     */
    public function test_team_member_cannot_move_task_of_other_team(): void
    {
        $data = $this->createProjectSetup();

        $teamA = Team::create(['project_id' => $data['project']->id, 'name' => 'Team A']);
        $teamB = Team::create(['project_id' => $data['project']->id, 'name' => 'Team B']);

        // member ada di Team A
        TeamMember::create([
            'team_id' => $teamA->id,
            'user_id' => $data['member']->id,
            'role_team' => 'member',
        ]);

        // task ini milik Team B
        $task = Task::create([
            'project_id' => $data['project']->id,
            'team_id' => $teamB->id,
            'status_id' => $data['todoStatus']->id,
            'created_by' => $data['owner']->id,
            'title' => 'Team B Task',
            'priority' => 'medium',
            'position' => 0,
        ]);

        $response = $this->actingAs($data['member'])
            ->putJson("/tasks/{$task->id}", [
                'status_id' => $data['inProgressStatus']->id,
                'position' => 0,
            ]);

        $response->assertStatus(403);
        $this->assertEquals($data['todoStatus']->id, Task::find($task->id)->status_id);
    }
}
