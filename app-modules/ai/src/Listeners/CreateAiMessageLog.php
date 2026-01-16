<?php

declare(strict_types=1);

namespace He4rt\Ai\Listeners;

use He4rt\Ai\Events\AiMessageCreated;

final class CreateAiMessageLog
{
    public function handle(AiMessageCreated $event): void
    {
        $message = $event->aiMessage;

        if (! $message->user || ! $message->request) {
            return;
        }

        //        LegacyAiMessageLog::create([
        //            'message' => $message->prompt ? "Starting smart prompt: {$message->prompt->title}" : $message->content,
        //            'metadata' => [
        //                'context' => $message->context,
        //            ],
        //            'request' => $message->request,
        //            'sent_at' => now(),
        //            'user_id' => $message->user_id,
        //            'ai_assistant_name' => $message->thread?->assistant?->name,
        //            'feature' => AiMessageLogFeature::Conversations,
        //        ]);
    }
}
