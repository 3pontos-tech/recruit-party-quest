<?php

declare(strict_types=1);

namespace He4rt\Feedback\Livewire;

use Livewire\Attributes\On;

class Comment extends \Kirschbaum\Commentions\Livewire\Comment
{
    #[On('comment:reaction:toggled')]
    public function handleReactionToggledEvent(string $reaction, int|string $commentId): void
    {
        if ((string) $this->comment->getId() !== (string) $commentId) {
            return;
        }

        $this->toggleReaction($reaction);
    }
}
