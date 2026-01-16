# Filament v4 Admin Panel - Features Plan

## Overview

This document outlines the comprehensive feature plan for the `panel-admin` module, which serves as the administrative interface for the RPQ (Recruit Party Quest) recruitment platform. Built with Filament v4, this panel will provide full CRUD capabilities, dashboard analytics, and workflow management for all domain modules.

### Current State

- ✅ Users Resource (CRUD, search, bulk delete)
- ✅ Teams Resource (CRUD, Members RelationManager)
- ✅ Roles Resource (CRUD, permissions management)

### Target State

Complete administrative control over all recruitment workflows with dashboards, analytics, and advanced actions.

---

## 1. Dashboard & Widgets

### 1.1 Main Dashboard Widgets

| Widget                          | Type             | Description                                            | Priority |
| ------------------------------- | ---------------- | ------------------------------------------------------ | -------- |
| `RecruitmentOverviewStats`      | StatsOverview    | Open requisitions, active applications, pending offers | P0       |
| `ApplicationsPerStatusChart`    | Chart (Doughnut) | Applications by status (New, InReview, etc.)           | P0       |
| `RequisitionsByDepartmentChart` | Chart (Bar)      | Open positions per department                          | P1       |
| `RecentApplicationsTable`       | Table            | Last 10 applications with quick actions                | P0       |
| `PendingEvaluationsWidget`      | Table            | Evaluations pending for current user                   | P1       |
| `TimeToHireChart`               | Chart (Line)     | Average days from application to hire (30d)            | P2       |
| `SourceEffectivenessChart`      | Chart (Pie)      | Applications by source (LinkedIn, Indeed, etc.)        | P2       |
| `UpcomingDeadlinesWidget`       | Table            | Offer response deadlines, requisition targets          | P1       |

### 1.2 Stats Overview Components

```php
// RecruitmentOverviewStats Widget
- Total Open Requisitions (Published status)
- Total Active Applications (New + InReview + InProgress)
- Pending Offers (OfferExtended status)
- This Month's Hires (Hired status, created_at this month)
- Knockout Failures Today (screening responses with is_knockout_fail)
```

---

## 2. Resources

### 2.1 Recruitment Module Resources

#### JobRequisitionResource

**Model:** `He4rt\Recruitment\Requisitions\Models\JobRequisition`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `id` | TextColumn | Copyable, hidden by default |
| `team.name` | TextColumn | Searchable, sortable |
| `department.name` | TextColumn | Searchable, sortable |
| `status` | BadgeColumn | Color from enum, filterable |
| `priority` | BadgeColumn | Color/icon from enum, filterable |
| `work_arrangement` | BadgeColumn | Icon from enum |
| `employment_type` | TextColumn | Filterable |
| `experience_level` | TextColumn | Filterable |
| `positions_available` | TextColumn | Sortable |
| `salary_range` | TextColumn | Formatted (min-max + currency) |
| `hiring_manager.name` | TextColumn | Searchable |
| `published_at` | TextColumn | Date, sortable |
| `created_at` | TextColumn | Date, sortable |

**Filters:**

- SelectFilter: `status` (RequisitionStatusEnum)
- SelectFilter: `priority` (RequisitionPriorityEnum)
- SelectFilter: `work_arrangement` (WorkArrangementEnum)
- SelectFilter: `employment_type` (EmploymentTypeEnum)
- SelectFilter: `experience_level` (ExperienceLevelEnum)
- SelectFilter: `team_id` (relationship)
- SelectFilter: `department_id` (relationship)
- TernaryFilter: `is_internal_only`
- TernaryFilter: `is_confidential`
- DateRangeFilter: `created_at`

**Form Schema:**

```
Section: Basic Information
├── Select: team_id (relationship)
├── Select: department_id (relationship, dependent on team)
├── Select: hiring_manager_id (relationship)
├── Select: status (RequisitionStatusEnum)
└── Select: priority (RequisitionPriorityEnum)

Section: Position Details
├── Select: work_arrangement (WorkArrangementEnum)
├── Select: employment_type (EmploymentTypeEnum)
├── Select: experience_level (ExperienceLevelEnum)
└── TextInput: positions_available (numeric)

Section: Compensation
├── TextInput: salary_range_min (numeric)
├── TextInput: salary_range_max (numeric)
└── TextInput: salary_currency

Section: Settings
├── Toggle: is_internal_only
├── Toggle: is_confidential
└── DateTimePicker: target_start_at

Section: Timestamps (Infolist only)
├── approved_at
├── published_at
└── closed_at
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `Approve` | HeaderAction | Set status to Approved, set approved_at |
| `Publish` | HeaderAction | Set status to Published, set published_at |
| `PutOnHold` | HeaderAction | Set status to OnHold |
| `Close` | HeaderAction | Set status to Closed, set closed_at |
| `Cancel` | HeaderAction | Set status to Cancelled |
| `Clone` | HeaderAction | Duplicate requisition with Draft status |
| `ViewApplications` | TableAction | Navigate to filtered applications |

**Relation Managers:**

- `PipelineStagesRelationManager` - Manage pipeline stages
- `ScreeningQuestionsRelationManager` - Manage screening questions
- `ApplicationsRelationManager` - View applications (read-only)

---

#### JobPostingResource

**Model:** `He4rt\Recruitment\Requisitions\Models\JobPosting`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `requisition.id` | TextColumn | Link to requisition |
| `title` | TextColumn | Searchable, sortable |
| `slug` | TextColumn | Copyable |
| `description` | TextColumn | Limit 50, toggleable |
| `location` | TextColumn | Searchable |
| `is_remote` | IconColumn | Boolean |
| `published_at` | TextColumn | Date, sortable |
| `expires_at` | TextColumn | Date, sortable |
| `application_count` | TextColumn | Aggregated count |

**Form Schema:**

```
Section: Posting Details
├── Select: requisition_id (relationship)
├── TextInput: title
├── TextInput: slug (auto-generated from title)
├── RichEditor: description
├── RichEditor: requirements
├── RichEditor: benefits
└── TextInput: location

Section: Settings
├── Toggle: is_remote
├── DateTimePicker: published_at
└── DateTimePicker: expires_at

Section: SEO (Collapsible)
├── TextInput: meta_title
└── Textarea: meta_description
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `Preview` | HeaderAction | Open public job page in new tab |
| `CopyLink` | TableAction | Copy public URL to clipboard |
| `Expire` | TableAction | Set expires_at to now |
| `Extend` | TableAction | Extend expires_at by 30 days |

---

#### StageResource (Pipeline Stages)

**Model:** `He4rt\Recruitment\Stages\Models\Stage`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `requisition.id` | TextColumn | Link |
| `name` | TextColumn | Searchable |
| `stage_type` | BadgeColumn | Color/icon from StageTypeEnum |
| `display_order` | TextColumn | Sortable |
| `expected_duration_days` | TextColumn | |
| `active` | IconColumn | Boolean, toggleable |

**Form Schema:**

```
├── Select: job_requisition_id (relationship)
├── TextInput: name
├── Select: stage_type (StageTypeEnum)
├── TextInput: display_order (numeric)
├── Textarea: description
├── TextInput: expected_duration_days (numeric)
└── Toggle: active
```

**Relation Managers:**

- `InterviewersRelationManager` - Assign interviewers to stage

---

### 2.2 Applications Module Resources

#### ApplicationResource

**Model:** `He4rt\Applications\Models\Application`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `tracking_code` | TextColumn | Searchable, copyable |
| `candidate.full_name` | TextColumn | Searchable, link to candidate |
| `requisition.id` | TextColumn | Link to requisition |
| `status` | BadgeColumn | Color from ApplicationStatusEnum, filterable |
| `source` | BadgeColumn | Color from CandidateSourceEnum |
| `currentStage.name` | TextColumn | Filterable |
| `screening_score` | TextColumn | Calculated from responses |
| `evaluations_count` | TextColumn | Aggregated |
| `offer_amount` | TextColumn | Money format, toggleable |
| `created_at` | TextColumn | Date, sortable |
| `rejected_at` | TextColumn | Date, toggleable |

**Filters:**

- SelectFilter: `status` (ApplicationStatusEnum)
- SelectFilter: `source` (CandidateSourceEnum)
- SelectFilter: `requisition_id` (relationship)
- SelectFilter: `current_stage_id` (relationship)
- TernaryFilter: `has_knockout_fail` (screening responses)
- DateRangeFilter: `created_at`
- DateRangeFilter: `rejected_at`
- DateRangeFilter: `offer_extended_at`

**Form Schema:**

```
Section: Application Info
├── Select: requisition_id (relationship, disabled on edit)
├── Select: candidate_id (relationship, disabled on edit)
├── Select: status (ApplicationStatusEnum)
├── Select: source (CandidateSourceEnum)
├── TextInput: source_details
└── Select: current_stage_id (relationship, filtered by requisition)

Section: Cover Letter
└── RichEditor: cover_letter

Section: Rejection (visible when status = Rejected)
├── DateTimePicker: rejected_at
├── Select: rejected_by (relationship)
├── Select: rejection_reason_category (RejectionReasonCategoryEnum)
└── Textarea: rejection_reason_details

Section: Offer (visible when status in [OfferExtended, OfferAccepted, OfferDeclined])
├── DateTimePicker: offer_extended_at
├── Select: offer_extended_by (relationship)
├── TextInput: offer_amount (money)
└── DateTimePicker: offer_response_deadline
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `MoveToStage` | TableAction | Modal to select stage, creates history |
| `Reject` | TableAction | Modal with reason category/details |
| `ExtendOffer` | TableAction | Modal with offer amount/deadline |
| `MarkHired` | TableAction | Set status to Hired |
| `Withdraw` | TableAction | Set status to Withdrawn |
| `SendEmail` | TableAction | Open communication modal |
| `ViewCandidate` | TableAction | Link to candidate profile |
| `DownloadResume` | TableAction | Download candidate resume |

**Bulk Actions:**

- `BulkReject` - Reject multiple applications
- `BulkMoveToStage` - Move multiple to same stage
- `BulkExport` - Export to CSV/Excel

**Relation Managers:**

- `ScreeningResponsesRelationManager` - View/edit screening answers
- `EvaluationsRelationManager` - View/add evaluations
- `CommentsRelationManager` - View/add comments
- `StageHistoryRelationManager` - View stage transitions (read-only)

---

#### ApplicationStageHistoryResource (Read-only, nested)

**Model:** `He4rt\Applications\Models\ApplicationStageHistory`

> This is primarily used as a RelationManager, not standalone resource.

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `fromStage.name` | TextColumn | Nullable (initial) |
| `toStage.name` | TextColumn | |
| `movedBy.name` | TextColumn | |
| `notes` | TextColumn | Limit 50 |
| `created_at` | TextColumn | DateTime, sortable |

---

### 2.3 Candidates Module Resources

#### CandidateResource

**Model:** `He4rt\Candidates\Models\Candidate`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `user.name` | TextColumn | Searchable, link |
| `user.email` | TextColumn | Searchable |
| `phone` | TextColumn | |
| `headline` | TextColumn | Limit 30 |
| `availability_date` | TextColumn | Date |
| `expected_salary` | TextColumn | Money format |
| `skills_count` | TextColumn | Aggregated |
| `applications_count` | TextColumn | Aggregated |
| `created_at` | TextColumn | Date, sortable |

**Filters:**

- SelectFilter: Skills (many-to-many)
- SelectFilter: `expected_salary_currency`
- TernaryFilter: `is_willing_to_relocate`
- TernaryFilter: `is_open_to_remote`
- DateRangeFilter: `availability_date`
- DateRangeFilter: `created_at`

**Form Schema:**

```
Section: Personal Information
├── Select: user_id (relationship)
├── TextInput: phone
├── RichEditor: bio
└── TextInput: headline

Section: Preferences
├── DatePicker: availability_date
├── TextInput: expected_salary (numeric)
├── TextInput: expected_salary_currency
├── Toggle: is_willing_to_relocate
└── Toggle: is_open_to_remote

Section: Links
├── TextInput: linkedin_url
├── TextInput: github_url
├── TextInput: portfolio_url
└── TextInput: website_url
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `CreateApplication` | HeaderAction | Create application for this candidate |
| `AddToTalentPool` | TableAction | Add to talent pool |
| `SendEmail` | TableAction | Open email modal |
| `DownloadResume` | TableAction | Download resume file |
| `MergeCandidate` | TableAction | Merge duplicate profiles |

**Relation Managers:**

- `SkillsRelationManager` - Manage skills (attach/detach with proficiency)
- `EducationRelationManager` - Manage education history
- `WorkExperienceRelationManager` - Manage work history
- `ApplicationsRelationManager` - View applications (read-only)
- `AddressRelationManager` - Manage addresses

---

#### SkillResource

**Model:** `He4rt\Candidates\Models\Skill`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `name` | TextColumn | Searchable, sortable |
| `category` | BadgeColumn | Color from CandidateSkillCategory |
| `candidates_count` | TextColumn | Aggregated |
| `created_at` | TextColumn | Date |

**Form Schema:**

```
├── TextInput: name
└── Select: category (CandidateSkillCategory)
```

**Bulk Actions:**

- `BulkDelete` - Delete unused skills
- `BulkMerge` - Merge duplicate skills

---

#### EducationResource (Nested/RelationManager)

**Model:** `He4rt\Candidates\Models\Education`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `institution` | TextColumn | Searchable |
| `degree` | TextColumn | |
| `field_of_study` | TextColumn | |
| `start_date` | TextColumn | Date |
| `end_date` | TextColumn | Date, nullable |
| `is_current` | IconColumn | Boolean |

**Form Schema:**

```
├── TextInput: institution
├── TextInput: degree
├── TextInput: field_of_study
├── DatePicker: start_date
├── DatePicker: end_date
├── Toggle: is_current
├── Textarea: description
└── TextInput: gpa
```

---

#### WorkExperienceResource (Nested/RelationManager)

**Model:** `He4rt\Candidates\Models\WorkExperience`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `company` | TextColumn | Searchable |
| `title` | TextColumn | Searchable |
| `location` | TextColumn | |
| `start_date` | TextColumn | Date |
| `end_date` | TextColumn | Date, nullable |
| `is_current` | IconColumn | Boolean |

**Form Schema:**

```
├── TextInput: company
├── TextInput: title
├── TextInput: location
├── DatePicker: start_date
├── DatePicker: end_date
├── Toggle: is_current
└── RichEditor: description
```

---

### 2.4 Screening Module Resources

#### ScreeningQuestionResource

**Model:** `He4rt\Screening\Models\ScreeningQuestion`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `requisition.id` | TextColumn | Link, filterable |
| `question_text` | TextColumn | Searchable, limit 50 |
| `question_type` | BadgeColumn | From QuestionTypeEnum |
| `is_required` | IconColumn | Boolean |
| `is_knockout` | IconColumn | Boolean, highlighted |
| `display_order` | TextColumn | Sortable |
| `responses_count` | TextColumn | Aggregated |

**Filters:**

- SelectFilter: `requisition_id` (relationship)
- SelectFilter: `question_type` (QuestionTypeEnum)
- TernaryFilter: `is_required`
- TernaryFilter: `is_knockout`

**Form Schema:**

```
Section: Question Details
├── Select: requisition_id (relationship)
├── Textarea: question_text
├── Select: question_type (QuestionTypeEnum)
└── TextInput: display_order (numeric)

Section: Options (visible for SingleChoice/MultipleChoice)
└── Repeater: choices
    ├── TextInput: value
    └── TextInput: label

Section: Validation
├── Toggle: is_required
├── Toggle: is_knockout
└── KeyValue: knockout_criteria (visible when is_knockout)
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `Duplicate` | TableAction | Copy question to same/other requisition |
| `Preview` | TableAction | Show question preview modal |
| `Reorder` | BulkAction | Reorder multiple questions |

**Relation Managers:**

- `ResponsesRelationManager` - View responses (read-only)

---

#### ScreeningResponseResource (Read-only)

**Model:** `He4rt\Screening\Models\ScreeningResponse`

> Primarily accessed via ApplicationResource -> ScreeningResponsesRelationManager

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `application.tracking_code` | TextColumn | Link |
| `question.question_text` | TextColumn | Limit 30 |
| `response_value` | TextColumn | JSON formatted |
| `is_knockout_fail` | IconColumn | Boolean, red highlight |
| `created_at` | TextColumn | DateTime |

**Filters:**

- SelectFilter: `application_id`
- SelectFilter: `question_id`
- TernaryFilter: `is_knockout_fail`

---

### 2.5 Feedback Module Resources

#### EvaluationResource

**Model:** `He4rt\Feedback\Models\Evaluation`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `application.tracking_code` | TextColumn | Link |
| `application.candidate.full_name` | TextColumn | |
| `stage.name` | TextColumn | |
| `evaluator.name` | TextColumn | Filterable |
| `overall_rating` | BadgeColumn | Color from EvaluationRatingEnum |
| `recommendation` | TextColumn | Limit 30 |
| `submitted_at` | TextColumn | DateTime, sortable |

**Filters:**

- SelectFilter: `application_id`
- SelectFilter: `stage_id`
- SelectFilter: `evaluator_id`
- SelectFilter: `overall_rating` (EvaluationRatingEnum)
- DateRangeFilter: `submitted_at`

**Form Schema:**

```
Section: Context
├── Select: application_id (relationship)
├── Select: stage_id (relationship)
└── Select: evaluator_id (relationship, default to current user)

Section: Rating
└── Radio: overall_rating (EvaluationRatingEnum with colors)

Section: Detailed Feedback
├── RichEditor: strengths
├── RichEditor: concerns
├── RichEditor: recommendation
└── RichEditor: notes

Section: Criteria Scores (optional)
└── KeyValue: criteria_scores
```

**Actions:**
| Action | Type | Description |
|--------|------|-------------|
| `ViewApplication` | TableAction | Navigate to application |
| `EditEvaluation` | TableAction | Edit existing evaluation |
| `DeleteEvaluation` | TableAction | Soft delete |

---

#### ApplicationCommentResource (Nested/RelationManager)

**Model:** `He4rt\Feedback\Models\ApplicationComment`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `application.tracking_code` | TextColumn | Link |
| `author.name` | TextColumn | |
| `content` | TextColumn | Limit 50 |
| `is_internal` | IconColumn | Boolean |
| `created_at` | TextColumn | DateTime, sortable |

**Form Schema:**

```
├── Select: application_id (relationship, hidden in relation manager)
├── Hidden: author_id (default current user)
├── RichEditor: content
└── Toggle: is_internal (default true)
```

---

### 2.6 Teams & Organization Resources (Enhanced)

#### DepartmentResource

**Model:** `He4rt\Teams\Department`

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `team.name` | TextColumn | Filterable |
| `name` | TextColumn | Searchable |
| `headUser.name` | TextColumn | |
| `requisitions_count` | TextColumn | Aggregated |
| `created_at` | TextColumn | Date |

**Form Schema:**

```
├── Select: team_id (relationship)
├── TextInput: name
├── Select: head_user_id (relationship, filtered by team members)
└── Textarea: description
```

**Relation Managers:**

- `RequisitionsRelationManager` - View department requisitions

---

### 2.7 Location Module Resources

#### AddressResource (Nested/RelationManager)

**Model:** `He4rt\Location\Address`

> Used as RelationManager on Team, Candidate resources

**Table Columns:**
| Column | Type | Features |
|--------|------|----------|
| `label` | TextColumn | (Home, Work, etc.) |
| `street_address` | TextColumn | |
| `city` | TextColumn | Searchable |
| `state` | TextColumn | |
| `postal_code` | TextColumn | |
| `country` | TextColumn | Filterable |
| `is_primary` | IconColumn | Boolean |

**Form Schema:**

```
├── TextInput: label
├── TextInput: street_address
├── TextInput: street_address_2
├── TextInput: city
├── TextInput: state
├── TextInput: postal_code
├── Select: country (country list)
└── Toggle: is_primary
```

---

## 3. Navigation Structure & Clusters

### 3.1 Navigation Groups

```
📊 Dashboard
    └── Main Dashboard

👥 People Management (Cluster)
    ├── Users
    ├── Teams
    ├── Departments
    └── Candidates

📋 Recruitment (Cluster)
    ├── Job Requisitions
    ├── Job Postings
    ├── Pipeline Stages
    └── Screening Questions

📝 Applications (Cluster)
    ├── All Applications
    ├── Pending Review
    ├── In Progress
    ├── Offers
    └── Evaluations

🔒 Access Control (Cluster)
    ├── Roles
    └── Permissions

📈 Reports (Cluster)
    ├── Hiring Analytics
    ├── Source Effectiveness
    ├── Time to Hire
    └── Pipeline Funnel

⚙️ Settings (Cluster)
    ├── Skills Library
    └── System Configuration
```

### 3.2 Cluster Definitions

#### PeopleCluster

```php
namespace He4rt\Admin\Filament\Clusters;

class PeopleCluster extends Cluster
{
    protected static ?string $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?int $navigationSort = 1;

    // Contains: UserResource, TeamResource, DepartmentResource, CandidateResource
}
```

#### RecruitmentCluster

```php
class RecruitmentCluster extends Cluster
{
    protected static ?string $navigationIcon = Heroicon::OutlinedBriefcase;
    protected static ?int $navigationSort = 2;

    // Contains: JobRequisitionResource, JobPostingResource, StageResource, ScreeningQuestionResource
}
```

#### ApplicationsCluster

```php
class ApplicationsCluster extends Cluster
{
    protected static ?string $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 3;

    // Contains: ApplicationResource, EvaluationResource
    // Custom filtered pages for status-specific views
}
```

#### AccessControlCluster

```php
class AccessControlCluster extends Cluster
{
    protected static ?string $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static ?int $navigationSort = 4;

    // Contains: RoleResource (existing)
    // PermissionResource (view-only)
}
```

#### ReportsCluster

```php
class ReportsCluster extends Cluster
{
    protected static ?string $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?int $navigationSort = 5;

    // Contains: Custom report pages
}
```

---

## 4. Custom Pages

### 4.1 Dashboard Pages

#### MainDashboard

**Path:** `/admin`
**Description:** Overview of recruitment metrics and quick actions

**Widgets:**

- RecruitmentOverviewStats
- ApplicationsPerStatusChart
- RecentApplicationsTable
- PendingEvaluationsWidget

---

### 4.2 Report Pages

#### HiringAnalyticsPage

**Path:** `/admin/reports/hiring-analytics`

**Features:**

- Date range selector
- Hires by month chart
- Average time-to-hire metric
- Offers acceptance rate
- Export to PDF/CSV

#### SourceEffectivenessPage

**Path:** `/admin/reports/source-effectiveness`

**Features:**

- Applications by source chart
- Conversion rates by source
- Cost per hire by source (if budget data available)
- Source comparison table

#### PipelineFunnelPage

**Path:** `/admin/reports/pipeline-funnel`

**Features:**

- Funnel visualization
- Drop-off rates between stages
- Average time in each stage
- Bottleneck identification

---

### 4.3 Workflow Pages

#### ApplicationKanbanPage

**Path:** `/admin/applications/kanban`

**Features:**

- Drag-and-drop between stages
- Quick actions on cards
- Filter by requisition
- Real-time updates

#### BulkApplicationReviewPage

**Path:** `/admin/applications/bulk-review`

**Features:**

- Multi-select applications
- Bulk status change
- Bulk stage move
- Bulk rejection with template

#### InterviewSchedulingPage

**Path:** `/admin/interviews/schedule`

**Features:**

- Calendar view
- Interviewer availability
- Candidate time preferences
- Email notifications

---

### 4.4 Configuration Pages

#### ScreeningQuestionBuilderPage

**Path:** `/admin/recruitment/{requisition}/screening-builder`

**Features:**

- Visual question builder
- Drag-and-drop reordering
- Question preview
- Import from template library

#### PipelineBuilderPage

**Path:** `/admin/recruitment/{requisition}/pipeline-builder`

**Features:**

- Visual pipeline editor
- Stage templates
- Interviewer assignment
- Duration configuration

---

## 5. Global Actions

### 5.1 Header Actions (Global Search Results)

| Action                 | Context                   | Description                    |
| ---------------------- | ------------------------- | ------------------------------ |
| `QuickViewApplication` | Application search result | Modal with application summary |
| `QuickViewCandidate`   | Candidate search result   | Modal with candidate profile   |
| `QuickViewRequisition` | Requisition search result | Modal with requisition details |

### 5.2 Global Search Configuration

```php
// Searchable resources with custom result formatting
- Applications: tracking_code, candidate name, requisition title
- Candidates: name, email, phone, skills
- Job Requisitions: id, department, hiring manager
- Users: name, email
- Teams: name
```

---

## 6. Access Control Integration

### 6.1 Resource Policies

Each resource should implement authorization using existing policies:

| Resource                 | Policy                 | Required Permissions           |
| ------------------------ | ---------------------- | ------------------------------ |
| `UserResource`           | `UserPolicy`           | users.view, users.create, etc. |
| `TeamResource`           | `TeamPolicy`           | teams.view, teams.create, etc. |
| `RoleResource`           | `RolePolicy`           | roles.view, roles.create, etc. |
| `JobRequisitionResource` | `JobRequisitionPolicy` | requisitions.view, etc.        |
| `ApplicationResource`    | `ApplicationPolicy`    | applications.view, etc.        |
| `CandidateResource`      | `CandidatePolicy`      | candidates.view, etc.          |
| `EvaluationResource`     | `EvaluationPolicy`     | evaluations.view, etc.         |

### 6.2 Role-Based Navigation

```php
// SuperAdmin: Full access to all resources
// Recruiter: Requisitions, Applications, Candidates, Evaluations
// Hiring Manager: Own requisitions, Related applications, Evaluations
// Interviewer: Assigned applications, Submit evaluations
// User: View-only access to assigned items
```

### 6.3 Multi-tenancy Scoping

All resources should be scoped to the current team:

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->whereBelongsTo(Filament::getTenant());
}
```

---

## 7. Implementation Priorities

### Phase 1: Core CRUD (P0) - Week 1-2

| Priority | Task                   | Status  |
| -------- | ---------------------- | ------- |
| P0       | UserResource           | ✅ Done |
| P0       | TeamResource           | ✅ Done |
| P0       | RoleResource           | ✅ Done |
| P0       | DepartmentResource     | ✅ Done |
| P0       | JobRequisitionResource | ✅ Done |
| P0       | CandidateResource      | ✅ Done |
| P0       | ApplicationResource    | ✅ Done |

### Phase 2: Relations & Actions (P1) - Week 3-4

| Priority | Task                              | Status     |
| -------- | --------------------------------- | ---------- |
| P1       | PipelineStagesRelationManager     | ⏳ Pending |
| P1       | ScreeningQuestionsRelationManager | ⏳ Pending |
| P1       | EvaluationsRelationManager        | ⏳ Pending |
| P1       | Application workflow actions      | ⏳ Pending |
| P1       | Requisition status actions        | ⏳ Pending |
| P1       | Bulk actions                      | ⏳ Pending |

### Phase 3: Dashboard & Widgets (P1) - Week 5

| Priority | Task                       | Status     |
| -------- | -------------------------- | ---------- |
| P1       | RecruitmentOverviewStats   | ⏳ Pending |
| P1       | ApplicationsPerStatusChart | ⏳ Pending |
| P1       | RecentApplicationsTable    | ⏳ Pending |
| P1       | PendingEvaluationsWidget   | ⏳ Pending |

### Phase 4: Advanced Features (P2) - Week 6-8

| Priority | Task                       | Status     |
| -------- | -------------------------- | ---------- |
| P2       | Clusters implementation    | ⏳ Pending |
| P2       | Application Kanban page    | ⏳ Pending |
| P2       | Report pages               | ⏳ Pending |
| P2       | Global search optimization | ⏳ Pending |
| P2       | Export functionality       | ⏳ Pending |

### Phase 5: Polish & UX (P3) - Week 9-10

| Priority | Task                | Status     |
| -------- | ------------------- | ---------- |
| P3       | Custom themes       | ⏳ Pending |
| P3       | Notification center | ⏳ Pending |
| P3       | Activity log viewer | ⏳ Pending |
| P3       | User impersonation  | ⏳ Pending |
| P3       | Keyboard shortcuts  | ⏳ Pending |

---

## 8. Testing Strategy

### 8.1 Test Structure

```
tests/Feature/Filament/
├── Resources/
│   ├── UserResourceTest.php ✅
│   ├── TeamResourceTest.php ✅
│   ├── RoleResourceTest.php ✅
│   ├── JobRequisitionResourceTest.php
│   ├── ApplicationResourceTest.php
│   ├── CandidateResourceTest.php
│   └── ...
├── Pages/
│   ├── DashboardTest.php
│   ├── ApplicationKanbanTest.php
│   └── ...
├── Widgets/
│   ├── RecruitmentOverviewStatsTest.php
│   └── ...
└── Actions/
    ├── MoveToStageActionTest.php
    ├── RejectApplicationActionTest.php
    └── ...
```

### 8.2 Test Coverage Requirements

| Area                 | Minimum Coverage |
| -------------------- | ---------------- |
| Resource List Pages  | 100%             |
| Resource Create/Edit | 100%             |
| Resource Actions     | 100%             |
| Relation Managers    | 90%              |
| Widgets              | 80%              |
| Custom Pages         | 80%              |

### 8.3 Test Patterns

```php
// Standard resource test pattern
beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    actingAs(User::factory()->create());
    artisan('sync:permissions');
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list records', function (): void {
    $records = Model::factory()->count(5)->create();

    livewire(ListResource::class)
        ->assertCanSeeTableRecords($records);
});

it('can create record', function (): void {
    livewire(CreateResource::class)
        ->fillForm([...])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('table', [...]);
});

it('can execute action', function (): void {
    $record = Model::factory()->create();

    livewire(ListResource::class)
        ->callTableAction('actionName', $record)
        ->assertNotified();

    expect($record->refresh())->status->toBe(ExpectedStatus);
});
```

---

## 9. File Structure

```
app-modules/panel-admin/
├── src/
│   └── Filament/
│       ├── Clusters/
│       │   ├── PeopleCluster.php
│       │   ├── RecruitmentCluster.php
│       │   ├── ApplicationsCluster.php
│       │   ├── AccessControlCluster.php
│       │   └── ReportsCluster.php
│       ├── Pages/
│       │   ├── Dashboard.php
│       │   └── Reports/
│       │       ├── HiringAnalyticsPage.php
│       │       ├── SourceEffectivenessPage.php
│       │       └── PipelineFunnelPage.php
│       ├── Resources/
│       │   ├── Applications/
│       │   │   ├── ApplicationResource.php
│       │   │   ├── Pages/
│       │   │   ├── RelationManagers/
│       │   │   ├── Schemas/
│       │   │   └── Tables/
│       │   ├── Candidates/
│       │   │   ├── CandidateResource.php
│       │   │   ├── SkillResource.php
│       │   │   └── ...
│       │   ├── Feedback/
│       │   │   ├── EvaluationResource.php
│       │   │   └── ...
│       │   ├── Permissions/ ✅
│       │   ├── Recruitment/
│       │   │   ├── JobRequisitionResource.php
│       │   │   ├── JobPostingResource.php
│       │   │   ├── StageResource.php
│       │   │   └── ...
│       │   ├── Screening/
│       │   │   ├── ScreeningQuestionResource.php
│       │   │   └── ...
│       │   ├── Teams/ ✅
│       │   └── Users/ ✅
│       └── Widgets/
│           ├── RecruitmentOverviewStats.php
│           ├── ApplicationsPerStatusChart.php
│           ├── RecentApplicationsTable.php
│           ├── PendingEvaluationsWidget.php
│           └── ...
├── resources/
│   └── views/
│       └── components/
│           └── ... (custom Blade components)
├── lang/
│   ├── en/
│   │   └── filament.php
│   └── pt_BR/
│       └── filament.php
└── tests/
    └── Feature/
        └── Filament/
            ├── RoleResourceTest.php ✅
            ├── TeamResourceTest.php ✅
            ├── UserResourceTest.php ✅
            └── ... (new tests)
```

---

## 10. Appendix: Enum Quick Reference

### ApplicationStatusEnum

| Value            | Label          | Color  |
| ---------------- | -------------- | ------ |
| `new`            | New            | Gray   |
| `in_review`      | In Review      | Blue   |
| `in_progress`    | In Progress    | Yellow |
| `offer_extended` | Offer Extended | Purple |
| `offer_accepted` | Offer Accepted | Green  |
| `offer_declined` | Offer Declined | Red    |
| `hired`          | Hired          | Green  |
| `rejected`       | Rejected       | Red    |
| `withdrawn`      | Withdrawn      | Gray   |

### RequisitionStatusEnum

| Value              | Label            | Color  |
| ------------------ | ---------------- | ------ |
| `draft`            | Draft            | Gray   |
| `pending_approval` | Pending Approval | Yellow |
| `approved`         | Approved         | Blue   |
| `published`        | Published        | Green  |
| `on_hold`          | On Hold          | Orange |
| `closed`           | Closed           | Gray   |
| `cancelled`        | Cancelled        | Red    |

### EvaluationRatingEnum

| Value        | Label      | Color  |
| ------------ | ---------- | ------ |
| `strong_no`  | Strong No  | Red    |
| `no`         | No         | Orange |
| `maybe`      | Maybe      | Yellow |
| `yes`        | Yes        | Blue   |
| `strong_yes` | Strong Yes | Green  |

### QuestionTypeEnum

| Value             | Label           |
| ----------------- | --------------- |
| `yes_no`          | Yes/No          |
| `text`            | Text            |
| `number`          | Number          |
| `single_choice`   | Single Choice   |
| `multiple_choice` | Multiple Choice |
| `file_upload`     | File Upload     |

### CandidateSourceEnum

| Value                | Label              |
| -------------------- | ------------------ |
| `linkedin`           | LinkedIn           |
| `indeed`             | Indeed             |
| `referral`           | Referral           |
| `company_website`    | Company Website    |
| `job_board`          | Job Board          |
| `recruitment_agency` | Recruitment Agency |
| `social_media`       | Social Media       |
| `career_fair`        | Career Fair        |
| `internal`           | Internal           |
| `other`              | Other              |

---

## 11. Notes & Considerations

### Performance Optimization

- Use eager loading in all resource queries
- Implement pagination (default 25 records)
- Use `->searchable(isIndividual: true)` sparingly
- Cache widget data where appropriate

### UX Guidelines

- Use consistent iconography (Heroicons)
- Follow color conventions from enums
- Provide confirmation modals for destructive actions
- Show loading states for async operations
- Use notifications for action feedback

### Accessibility

- Ensure all forms have proper labels
- Provide keyboard navigation
- Use ARIA attributes where needed
- Maintain color contrast ratios

### Internationalization

- All strings should use translation keys
- Date/time formatting per locale
- Currency formatting per locale
- RTL support consideration
