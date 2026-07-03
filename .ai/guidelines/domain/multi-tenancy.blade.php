# Multi-Tenancy

Filament-native tenancy. **The tenant is a `Team`** (the organization a user belongs to),
owned by the `teams` module. There is no separate `Tenant` model and no global
tenant-scope middleware — tenancy is driven by a trait on the `User` model plus Filament's
panel resolution.

## Resolution

Filament resolves the tenant from the URL slug (`/{panel}/{team-slug}/…`). Only the
**organization** panel enables tenancy; it is configured in the App-layer panel provider
(not in a module), at `app/Providers/Filament/OrganizationPanelProvider.php`:

@verbatim
<code-snippet name="Panel tenancy" lang="php">
// OrganizationPanelProvider
$panel->tenant(model: Team::class, slugAttribute: 'slug');
</code-snippet>
@endverbatim

The **admin** and **app** panels are **not** tenant-scoped.

## The `InteractsWithTenants` trait

`User` implements Filament's `HasTenants` contract via
`He4rt\Teams\Concerns\InteractsWithTenants`. The trait provides the tenant graph and
access checks:

- `teams(): BelongsToMany<Team>` — teams the user belongs to (`team_user` pivot, `TeamMember`).
- `ownedTenants(): HasMany<Team>` — teams the user owns (`owner_id`).
- `getTenants(Panel): Collection<Team>` — tenants the user may switch into.
- `canAccessTenant(Model): bool` — per-tenant access check.

**Role bypass:** users with `Roles::SuperAdmin` or `Roles::Admin` see and can access
**every** team; everyone else is limited to the teams they belong to or own.

## Data isolation

Filament auto-scopes any Resource registered in the tenant-enabled organization panel.
Tenant-scoped models associate with a `Team`; before adding a new one, copy the
association pattern from an existing sibling model in the same panel rather than inventing
a new scoping mechanism. For anything outside Filament's automatic path, scope explicitly
(e.g. `whereBelongsTo(filament()->getTenant())`).

## In tests

@verbatim
<code-snippet name="Tenant-scoped test setup" lang="php">
$team = Team::factory()->create();
$user = User::factory()->create();

filament()->setCurrentPanel('organization');
filament()->setTenant($team);
$this->actingAs($user);

// Propagate the tenant through factory chains:
SomeModel::factory()->recycle($team)->recycle($user)->create();
</code-snippet>
@endverbatim

Use `->recycle($team)` to reuse the same tenant across a factory chain, and
`filament()->setTenant($team)` to activate it for the panel under test.

> Note: `UserObserver` auto-creates a `Candidate` for every new `User` — keep that in mind
> when a test recycles a `User` and also expects a specific `Candidate`.
