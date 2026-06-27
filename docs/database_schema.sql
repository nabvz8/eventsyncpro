-- ============================================================================
-- SQL DDL SCHEMA DESIGN FOR EVENTSYNC PRO (POSTGRESQL)
-- ============================================================================

-- Mengaktifkan ekstensi UUID generator bawaan PostgreSQL jika diperlukan
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. ENUM TYPES DEFINITIONS
CREATE TYPE task_priority AS ENUM ('low', 'medium', 'high');
CREATE TYPE global_role AS ENUM ('owner_apps', 'user');
CREATE TYPE workspace_role AS ENUM ('owner', 'admin', 'member');
CREATE TYPE invitation_status AS ENUM ('pending', 'accepted', 'expired');
CREATE TYPE project_role AS ENUM ('leader', 'co_leader');
CREATE TYPE team_role AS ENUM ('leader', 'co_leader', 'member');

-- ============================================================================
-- 2. TABLES CREATION
-- ============================================================================

-- A. TABEL USERS
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(500),
    role_global global_role NOT NULL DEFAULT 'user',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- B. TABEL WORKSPACES
CREATE TABLE workspaces (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    logo_url VARCHAR(500),
    owner_id UUID NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_workspaces_owner FOREIGN KEY (owner_id) 
        REFERENCES users (id) ON DELETE RESTRICT
);

-- C. TABEL WORKSPACE_MEMBERS
CREATE TABLE workspace_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID NOT NULL,
    user_id UUID NOT NULL,
    role_workspace workspace_role NOT NULL DEFAULT 'member',
    status_invitation invitation_status NOT NULL DEFAULT 'accepted',
    joined_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_workspace_members_workspace FOREIGN KEY (workspace_id) 
        REFERENCES workspaces (id) ON DELETE CASCADE,
    CONSTRAINT fk_workspace_members_user FOREIGN KEY (user_id) 
        REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT uq_workspace_user UNIQUE (workspace_id, user_id)
);

-- D. TABEL PROJECTS
CREATE TABLE projects (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by UUID,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_workspace FOREIGN KEY (workspace_id) 
        REFERENCES workspaces (id) ON DELETE CASCADE,
    CONSTRAINT fk_projects_creator FOREIGN KEY (created_by) 
        REFERENCES users (id) ON DELETE SET NULL
);

-- E. TABEL PROJECT_MEMBERS (Project Leader & Co-Leader)
CREATE TABLE project_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL,
    user_id UUID NOT NULL,
    role_project project_role NOT NULL,
    joined_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_members_project FOREIGN KEY (project_id) 
        REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_project_members_user FOREIGN KEY (user_id) 
        REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT uq_project_member UNIQUE (project_id, user_id)
);

-- F. TABEL TEAMS
CREATE TABLE teams (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teams_project FOREIGN KEY (project_id) 
        REFERENCES projects (id) ON DELETE CASCADE
);

-- G. TABEL TEAM_MEMBERS (Team Leader, Co-Leader, Member)
CREATE TABLE team_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    team_id UUID NOT NULL,
    user_id UUID NOT NULL,
    role_team team_role NOT NULL DEFAULT 'member',
    joined_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_team_members_team FOREIGN KEY (team_id) 
        REFERENCES teams (id) ON DELETE CASCADE,
    CONSTRAINT fk_team_members_user FOREIGN KEY (user_id) 
        REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT uq_team_user UNIQUE (team_id, user_id)
);

-- H. TABEL TASK_STATUSES (Kolom Status Kanban Per-Project)
CREATE TABLE task_statuses (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL,
    name VARCHAR(50) NOT NULL,
    color_hex VARCHAR(7) NOT NULL DEFAULT '#9E9E9E',
    position INT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_task_statuses_project FOREIGN KEY (project_id) 
        REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT uq_project_status_position UNIQUE (project_id, position)
);

-- I. TABEL TASKS
CREATE TABLE tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL,
    team_id UUID NOT NULL,
    status_id UUID NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date TIMESTAMP WITH TIME ZONE,
    due_date TIMESTAMP WITH TIME ZONE,
    priority task_priority NOT NULL DEFAULT 'medium',
    assignee_id UUID,
    parent_task_id UUID,
    created_by UUID,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) 
        REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_team FOREIGN KEY (team_id) 
        REFERENCES teams (id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_status FOREIGN KEY (status_id) 
        REFERENCES task_statuses (id) ON DELETE RESTRICT,
    CONSTRAINT fk_tasks_assignee FOREIGN KEY (assignee_id) 
        REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_parent FOREIGN KEY (parent_task_id) 
        REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_creator FOREIGN KEY (created_by) 
        REFERENCES users (id) ON DELETE SET NULL,
    -- Memastikan tanggal batas waktu tidak mendahului tanggal mulai
    CONSTRAINT chk_dates CHECK (due_date IS NULL OR start_date IS NULL OR due_date >= start_date)
);

-- J. TABEL TASK_COMMENTS
CREATE TABLE task_comments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    task_id UUID NOT NULL,
    user_id UUID NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_task_comments_task FOREIGN KEY (task_id) 
        REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_task_comments_user FOREIGN KEY (user_id) 
        REFERENCES users (id) ON DELETE CASCADE
);

-- K. TABEL TASK_ATTACHMENTS
CREATE TABLE task_attachments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    task_id UUID NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by UUID,
    uploaded_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_task_attachments_task FOREIGN KEY (task_id) 
        REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_task_attachments_user FOREIGN KEY (uploaded_by) 
        REFERENCES users (id) ON DELETE SET NULL
);

-- ============================================================================
-- 3. INDEXES DEFINITIONS (Untuk Optimasi Performa Query)
-- ============================================================================

-- Indeks Keanggotaan & Relasi Cepat
CREATE INDEX idx_workspace_members_user ON workspace_members(user_id);
CREATE INDEX idx_workspace_members_workspace ON workspace_members(workspace_id);
CREATE INDEX idx_project_members_project ON project_members(project_id);
CREATE INDEX idx_project_members_user ON project_members(user_id);
CREATE INDEX idx_team_members_team ON team_members(team_id);
CREATE INDEX idx_team_members_user ON team_members(user_id);

-- Indeks Hirarki Project & Task
CREATE INDEX idx_projects_workspace ON projects(workspace_id);
CREATE INDEX idx_teams_project ON teams(project_id);
CREATE INDEX idx_task_statuses_project ON task_statuses(project_id);
CREATE INDEX idx_tasks_project ON tasks(project_id);
CREATE INDEX idx_tasks_team ON tasks(team_id);
CREATE INDEX idx_tasks_status ON tasks(status_id);
CREATE INDEX idx_tasks_parent ON tasks(parent_task_id);
CREATE INDEX idx_tasks_assignee ON tasks(assignee_id);

-- Indeks Diskusi & File Pendukung
CREATE INDEX idx_task_comments_task ON task_comments(task_id);
CREATE INDEX idx_task_attachments_task ON task_attachments(task_id);

-- ============================================================================
-- 4. TRIGGERS & FUNCTIONS (Untuk Otomatisasi updated_at)
-- ============================================================================

-- Fungsi global untuk mengubah data updated_at ke waktu saat ini
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger Pemicu Update Kolom updated_at
CREATE TRIGGER trigger_update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_workspaces_updated_at
    BEFORE UPDATE ON workspaces
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_teams_updated_at
    BEFORE UPDATE ON teams
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_projects_updated_at
    BEFORE UPDATE ON projects
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_task_statuses_updated_at
    BEFORE UPDATE ON task_statuses
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_tasks_updated_at
    BEFORE UPDATE ON tasks
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
