<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Users\User;

it('can create an application comment', function (): void {
    $comment = ApplicationComment::factory()->create();

    expect($comment)->toBeInstanceOf(ApplicationComment::class)
        ->and($comment->id)->not->toBeNull()
        ->and($comment->content)->not->toBeNull();
});

it('can create an internal comment', function (): void {
    $comment = ApplicationComment::factory()->internal()->create();

    expect($comment->is_internal)->toBeTrue();
});

it('can create an external comment', function (): void {
    $comment = ApplicationComment::factory()->external()->create();

    expect($comment->is_internal)->toBeFalse();
});

it('defaults to internal comment', function (): void {
    $comment = ApplicationComment::factory()->create();

    expect($comment->is_internal)->toBeTrue();
});

it('belongs to an application', function (): void {
    $application = Application::factory()->create();
    $comment = ApplicationComment::factory()->create(['application_id' => $application->id]);

    expect($comment->application)->toBeInstanceOf(Application::class)
        ->and($comment->application->id)->toBe($application->id);
});

it('belongs to an author', function (): void {
    $user = User::factory()->create();
    $comment = ApplicationComment::factory()->create(['author_id' => $user->id]);

    expect($comment->author)->toBeInstanceOf(User::class)
        ->and($comment->author->id)->toBe($user->id);
});

it('can be accessed from application', function (): void {
    $application = Application::factory()->create();
    ApplicationComment::factory()->count(3)->create(['application_id' => $application->id]);

    expect($application->comments)->toHaveCount(3);
});

it('stores content as text', function (): void {
    $content = 'This is a detailed comment about the candidate performance during the interview.';
    $comment = ApplicationComment::factory()->create(['content' => $content]);

    expect($comment->content)->toBe($content);
});
