<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Schemas\Components\Component;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\Comment;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Organization\Listeners\SendMentionNotification;
use He4rt\Organization\Mail\MentionedInCommentMail;
use He4rt\Organization\Notifications\MentionedInCommentNotification;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Team;
use Illuminate\Support\Facades\Notification;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

/**
 * Recursively walks through Filament Schema component tree to find all instances of a given class.
 *
 * @template T of object
 *
 * @param  array<Component>  $components
 * @param  class-string<T>  $class
 * @return array<T>
 */
function findComponentsOfType(array $components, string $class): array
{
    $found = [];

    foreach ($components as $component) {
        if ($component instanceof $class) {
            $found[] = $component;
        }

        try {
            $childSchemas = $component->getChildComponentContainers(withHidden: true);
            foreach ($childSchemas as $childSchema) {
                $found = array_merge($found, findComponentsOfType($childSchema->getComponents(withHidden: true), $class));
            }
        } catch (Throwable) {
            // Component does not have children or threw
        }
    }

    return $found;
}

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    JobPosting::factory()->for($this->application->requisition, 'jobRequisition')->createOne();
    $this->team = $this->application->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    $this->recruiter->user->teams()->syncWithoutDetaching([$this->team->id]);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('has CommentsEntry in the application infolist schema', function (): void {
    $component = livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->instance();

    $topLevelComponents = $component->infolist->getComponents(withHidden: true);

    $commentsEntries = findComponentsOfType($topLevelComponents, CommentsEntry::class);

    expect($commentsEntries)->not->toBeEmpty('CommentsEntry was not found in the application infolist');
});

it('mentionables returns only members of the current team excluding the authenticated user', function (): void {
    // Create team members BEFORE rendering the Livewire component so
    // the mentionables closure (which uses once()) captures the correct set.
    $recruiterB = Recruiter::factory()->for($this->team, 'team')->create();
    $recruiterB->user->teams()->syncWithoutDetaching([$this->team->id]);

    $otherTeam = Team::factory()->create();
    $recruiterC = Recruiter::factory()->for($otherTeam, 'team')->create();
    $recruiterC->user->teams()->syncWithoutDetaching([$otherTeam->id]);

    $component = livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->instance();

    $topLevelComponents = $component->infolist->getComponents(withHidden: true);

    /** @var CommentsEntry[] $commentsEntries */
    $commentsEntries = findComponentsOfType($topLevelComponents, CommentsEntry::class);

    expect($commentsEntries)->not->toBeEmpty('CommentsEntry not found');

    $mentionables = $commentsEntries[0]->getMentionables();
    $mentionableIds = collect($mentionables)->pluck('id');

    expect($mentionableIds)
        ->toContain($recruiterB->user->id)
        ->not->toContain($recruiterC->user->id)
        ->not->toContain($this->recruiter->user->id);
});

it('sends MentionedInCommentNotification to mentioned user when listener handles the event', function (): void {
    Notification::fake();

    $author = Recruiter::factory()->for($this->team, 'team')->create();
    $mentionedUser = Recruiter::factory()->for($this->team, 'team')->create();

    $comment = Comment::factory()->forApplication($this->application)->authoredBy($author->user)->create();

    $event = new UserWasMentionedEvent($comment, $mentionedUser->user);

    // Call the listener handle() directly to bypass queued serialization
    $listener = new SendMentionNotification();
    $listener->handle($event);

    Notification::assertSentTo(
        $mentionedUser->user,
        MentionedInCommentNotification::class
    );
});

it('MentionedInCommentNotification toMail() returns a MentionedInCommentMail addressed to the mentioned user', function (): void {
    $author = Recruiter::factory()->for($this->team, 'team')->create();
    $mentionedUser = Recruiter::factory()->for($this->team, 'team')->create();

    $comment = Comment::factory()->forApplication($this->application)->authoredBy($author->user)->create();

    $notification = new MentionedInCommentNotification(
        comment: $comment,
        mentionedUser: $mentionedUser->user,
        tenantSlug: $this->team->slug,
    );

    $mail = $notification->toMail($mentionedUser->user);

    expect($mail)->toBeInstanceOf(MentionedInCommentMail::class)
        ->and($mail->mentionedUser->id)->toBe($mentionedUser->user->id)
        ->and($mail->comment->id)->toBe($comment->id)
        ->and($mail->tenantSlug)->toBe($this->team->slug);
});

it('comments are created with is_internal set to true', function (): void {
    $comment = Comment::factory()->forApplication($this->application)->authoredBy($this->recruiter->user)->create();

    expect($comment->fresh()->is_internal)->toBeTrue();
});

it('MentionedInCommentNotification toDatabase() builds a Filament notification with a link to the comments tab', function (): void {
    $author = Recruiter::factory()->for($this->team, 'team')->create();
    $mentionedUser = Recruiter::factory()->for($this->team, 'team')->create();

    $comment = Comment::factory()->forApplication($this->application)->authoredBy($author->user)->create();

    $payload = new MentionedInCommentNotification(
        comment: $comment,
        mentionedUser: $mentionedUser->user,
        tenantSlug: $this->team->slug,
    )->toDatabase($mentionedUser->user);

    expect($payload)
        ->toHaveKeys(['title', 'body', 'actions'])
        ->and($payload['actions'][0]['url'])->toContain('tab=comments');
});

it('mentioning a user in a comment body dispatches notification through the full SaveComment flow', function (): void {
    Notification::fake();

    $author = Recruiter::factory()->for($this->team, 'team')->create();
    $mentionedUser = Recruiter::factory()->for($this->team, 'team')->create();

    $body = sprintf(
        '<p>Oi, <span data-type="mention" data-id="%s">@%s</span> pode revisar?</p>',
        $mentionedUser->user->getKey(),
        $mentionedUser->user->name,
    );

    SaveComment::run($this->application, $author->user, $body);

    Notification::assertSentTo(
        $mentionedUser->user,
        MentionedInCommentNotification::class,
    );
});
