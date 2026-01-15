# Teams Module

## Overview

The **Teams** module manages organizational structure within the recruitment system. It handles teams (organizations/companies) and their departments, providing multi-tenancy support for the application.

## Context

This module represents the organizational hierarchy. Teams are the top-level tenant entities that own job requisitions, employ users, and manage candidates. Departments further subdivide teams for organizational clarity.

### Domain Position

```mermaid
graph TB
    subgraph Teams Module
        TEAM[Team]
        DEPT[Department]
    end

    subgraph External
        USER[Users Module]
        LOC[Location Module]
        REC[Recruitment Module]
    end

    USER -->|owns| TEAM
    USER -->|member of| TEAM
    USER -->|heads| DEPT
    LOC -->|provides address| TEAM
    TEAM -->|has| DEPT
    TEAM -->|owns| REC

    style TEAM stroke:#4299e1,stroke-width:3px
    style DEPT stroke:#4299e1,stroke-width:2px
```

## Models

### Team

The top-level organizational entity (company/organization).

| Property        | Type                 | Description             |
| --------------- | -------------------- | ----------------------- |
| `id`            | UUID                 | Primary key             |
| `name`          | string               | Team/company name       |
| `description`   | text                 | Team description        |
| `slug`          | string               | URL-friendly identifier |
| `owner_id`      | UUID                 | FK to owner user        |
| `status`        | TeamStatus           | Current status          |
| `contact_email` | string               | Primary contact email   |
| `deleted_at`    | timestamp (nullable) | Soft delete             |

**Relationships:**

- `belongsTo` User (owner)
- `belongsToMany` User (members)
- `hasMany` Department
- `morphMany` Address (via HasAddresses trait)

### Department

A subdivision within a team.

| Property       | Type                 | Description            |
| -------------- | -------------------- | ---------------------- |
| `id`           | UUID                 | Primary key            |
| `team_id`      | UUID                 | FK to parent team      |
| `name`         | string               | Department name        |
| `description`  | text                 | Department description |
| `head_user_id` | UUID                 | FK to department head  |
| `deleted_at`   | timestamp (nullable) | Soft delete            |

**Relationships:**

- `belongsTo` Team
- `belongsTo` User (headUser)

## Enums

### TeamStatus

```
Active    → Fully operational (🟢 Green)
Suspended → Temporarily disabled (🟡 Yellow)
Archived  → No longer active (🔴 Red)
```

Each status has:

- **Color**: Visual indicator
- **Icon**: Heroicon representation
- **Label**: Translatable label

## Organizational Hierarchy

```mermaid
graph TB
    subgraph Team/Organization
        T[Team]
        O[Owner]
        M1[Member 1]
        M2[Member 2]
        M3[Member 3]
    end

    subgraph Departments
        D1[Engineering]
        D2[Marketing]
        D3[HR]
    end

    subgraph Department Heads
        H1[Eng Head]
        H2[Mkt Head]
        H3[HR Head]
    end

    O -->|owns| T
    M1 -->|member| T
    M2 -->|member| T
    M3 -->|member| T

    T --> D1
    T --> D2
    T --> D3

    H1 -->|heads| D1
    H2 -->|heads| D2
    H3 -->|heads| D3

    style T stroke:#4299e1,stroke-width:3px
    style D1 stroke:#38b2ac,stroke-width:2px
    style D2 stroke:#38b2ac,stroke-width:2px
    style D3 stroke:#38b2ac,stroke-width:2px
```

## Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ TEAMS : "owns"
    USERS }o--o{ TEAMS : "member of"
    TEAMS ||--o{ DEPARTMENTS : "has"
    USERS ||--o{ DEPARTMENTS : "heads"
    TEAMS ||--o{ JOB_REQUISITIONS : "creates"
    DEPARTMENTS ||--o{ JOB_REQUISITIONS : "requests"

    TEAMS {
        uuid id PK
        string name
        string description
        string slug
        uuid owner_id FK
        enum status
        string contact_email
    }

    DEPARTMENTS {
        uuid id PK
        uuid team_id FK
        string name
        string description
        uuid head_user_id FK
    }

    TEAM_USER {
        uuid team_id FK
        uuid user_id FK
        timestamp created_at
    }
```

## Multi-Tenancy Support

The teams module provides multi-tenancy through the `InteractsWithTenants` trait:

```php
use He4rt\Teams\Concerns\InteractsWithTenants;

class SomeModel extends Model
{
    use InteractsWithTenants;
}
```

### Tenant Scoping

```mermaid
sequenceDiagram
    participant U as User
    participant T as Tenant Scope
    participant D as Database

    U->>T: Request Data
    T->>T: Get Current Team
    T->>D: Query with team_id filter
    D-->>T: Filtered Results
    T-->>U: Team-scoped Data
```

## Business Rules

### Team Management

1. **Ownership**: Every team must have an owner (a user)
2. **Membership**: Users can belong to multiple teams
3. **Status Control**: Teams can be suspended or archived
4. **Soft Deletes**: Teams are soft-deleted for data retention

### Department Management

1. **Team Scoped**: Departments always belong to a team
2. **Department Head**: Each department can have a designated head
3. **Requisition Link**: Job requisitions are tied to departments

### Team Membership

1. **Many-to-Many**: Users can be members of multiple teams
2. **Timestamps**: Membership timestamps are tracked
3. **Owner vs Member**: Owner is distinct from general membership

### Status Transitions

```mermaid
stateDiagram-v2
    [*] --> Active: Create Team

    Active --> Suspended: Suspend
    Active --> Archived: Archive

    Suspended --> Active: Reactivate
    Suspended --> Archived: Archive

    Archived --> Active: Restore

    Archived --> [*]: Delete
```

## Directory Structure

```
teams/
├── database/
│   ├── factories/
│   │   ├── DepartmentFactory.php
│   │   └── TeamFactory.php
│   └── migrations/
│       ├── 2026_01_14_164608_create_teams_table.php
│       ├── 2026_01_14_165028_create_team_user.php
│       └── 2026_01_15_163410_create_departments_table.php
├── lang/
│   ├── en/
│   │   ├── filament.php
│   │   └── team_status.php
│   └── pt_BR/
│       ├── filament.php
│       └── team_status.php
├── src/
│   ├── Concerns/
│   │   └── InteractsWithTenants.php
│   ├── Policies/
│   │   ├── DepartmentPolicy.php
│   │   └── TeamPolicy.php
│   ├── Department.php
│   ├── Team.php
│   ├── TeamsServiceProvider.php
│   └── TeamStatus.php
└── tests/
    └── Feature/
        └── TeamTest.php
```

## Usage Examples

### Creating a Team

```php
$team = Team::create([
    'name' => 'Acme Corporation',
    'description' => 'Leading software company',
    'slug' => 'acme-corp',
    'owner_id' => $user->id,
    'status' => TeamStatus::Active,
    'contact_email' => 'hr@acme.com',
]);
```

### Adding Members

```php
$team->members()->attach($user->id);

// Or with multiple users
$team->members()->attach([$user1->id, $user2->id, $user3->id]);
```

### Creating Departments

```php
$department = $team->departments()->create([
    'name' => 'Engineering',
    'description' => 'Software development team',
    'head_user_id' => $leadEngineer->id,
]);
```

### Querying Team Data

```php
// Get all active teams
Team::where('status', TeamStatus::Active)->get();

// Get team with members
$team->load('members', 'departments');

// Get user's teams
$user->teams; // via belongsToMany
```

## TODO / Future Enhancements

- [ ] Team invitations system
- [ ] Team settings/preferences
- [ ] Billing/subscription per team
- [ ] Team-level permissions
- [ ] Department hierarchy (sub-departments)
- [ ] Team activity dashboard
- [ ] Cross-team collaboration features
- [ ] Team branding (logo, colors)
