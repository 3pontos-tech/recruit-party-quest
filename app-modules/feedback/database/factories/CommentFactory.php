<?php

declare(strict_types=1);

namespace He4rt\Feedback\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\Comment;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Comment> */
final class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'body' => '<p>'.fake()->sentence().'</p>',
            'author_id' => User::factory(),
            'author_type' => (new User)->getMorphClass(),
            'commentable_id' => Application::factory(),
            'commentable_type' => (new Application)->getMorphClass(),
            'is_internal' => true,
        ];
    }

    public function forApplication(Application $application): self
    {
        return $this->state(fn () => [
            'commentable_id' => $application->getKey(),
            'commentable_type' => $application->getMorphClass(),
        ]);
    }

    public function authoredBy(User $user): self
    {
        return $this->state(fn () => [
            'author_id' => $user->getKey(),
            'author_type' => $user->getMorphClass(),
        ]);
    }

    /** Insere uma menção HTML no corpo no formato que getMentioned() detecta (data-id UUID). */
    public function mentioning(User $user): self
    {
        return $this->state(fn (array $attrs) => [
            'body' => $attrs['body'].sprintf(
                '<span data-type="mention" data-id="%s">@%s</span>',
                $user->getKey(),
                $user->name,
            ),
        ]);
    }
}
