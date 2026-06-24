# Presentation Layer

The `panel-*` modules (`panel-admin`, `panel-app`, `panel-organization`) are the
**presentation layer**. They own UI concerns: Filament Resources, Pages, Widgets, Livewire
components, and Blade views.

| Module               | Namespace            | Panel                                  |
| -------------------- | -------------------- | -------------------------------------- |
| `panel-admin`        | `He4rt\Admin`        | System administration                  |
| `panel-app`          | `He4rt\App`          | Candidate / applicant-facing           |
| `panel-organization` | `He4rt\Organization` | Tenant (Team) -facing, tenancy enabled |

The `he4rt` (`He4rt\Core`) module is a **UI kit** — shared Blade view components, CSS
(incl. the `3pontos` theme) and fonts the panels reuse. Despite the `Core` name it holds no
business logic; it is **not** the application core.

> The actual Filament `PanelProvider`s live in `app/Providers/Filament/` (the root `App\`
> namespace). The `panel-*` module `*ServiceProvider`s only do supporting setup (render
> hooks, view/translation registration). Tenancy is wired in `OrganizationPanelProvider` —
> see `multi-tenancy`.

## Rule

Domain logic (Actions, Models, DTOs, business rules) belongs in domain modules
(`applications`, `candidates`, `recruitment`, …), never in presentation modules.

Presentation modules import from domain modules.
Domain modules never import from presentation modules.

Always import domain classes into Pages, Resources, Widgets, and Livewire components with
`use` statements — this keeps the presentation layer decoupled from domain internals.

## Filament (v5)

Research Filament 5.x before implementing, using the `search-docs` MCP tool (or `context7`
if available).

When a Filament Action triggers domain behaviour, wrap a domain Action in a Filament Action
class. The Filament Action stays focused on UI; the domain Action does the work.

@verbatim
<code-snippet name="Filament Action wrapping a Domain Action" lang="php">
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Actions\RegisterOffer;
use He4rt\Applications\DTOs\OfferData;

class RegisterOfferAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::offers.actions.register.label'))
            ->icon(Heroicon::PlusCircle)
            ->action(function (array $data): void {
                resolve(RegisterOffer::class)->execute(OfferData::fromArray($data));

                Notification::make()->success()->send();
            });
    }
}
</code-snippet>
@endverbatim

When an action requires authorization, prefer `->authorize('ability')` on the action (it
handles both enforcement and visibility) over a manual `Gate` call.

## Livewire (v4)

When working in the presentation layer, activate any available Livewire skill before
building components, and follow the existing component conventions in the panel you are editing.
