<?php

declare(strict_types=1);

namespace He4rt\Ai\Jobs\Advisors;

use He4rt\Ai\Events\Advisors\AdvisorMessageChunk;
use He4rt\Ai\Events\Advisors\AdvisorMessageFinished;
use He4rt\Ai\Models\AiMessage;
use He4rt\Ai\Models\AiMessageFile;
use He4rt\Ai\Models\AiThread;
use He4rt\Ai\Models\Prompt;
use He4rt\Ai\Support\StreamingChunks\Finish;
use He4rt\Ai\Support\StreamingChunks\Meta;
use He4rt\Ai\Support\StreamingChunks\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SendAdvisorMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    /**
     * @param  array<string, mixed>  $request
     * @param  array<AiMessageFile>  $files
     */
    public function __construct(
        private AiThread $thread,
        private string|Prompt $content,
        private array $request = [],
        private array $files = [],
        private bool $hasImageGeneration = false,
    ) {}

    public function handle(): void
    {
        $message = new AiMessage;

        if ($this->content instanceof Prompt) {
            if ($this->content->is_smart) {
                $descriptionLine = $this->content->description
                    ? 'with the description '.$this->content->description
                    : null;

                $additionalContent = sprintf('Below I will provide you the input content for a prompt with the name %s, in the category %s', $this->content->title, $this->content->type->title).($descriptionLine ? ', '.$descriptionLine : '').'.
                The prompt may have variables {{ VARIABLE }} that are needed in order to effectively serve your function. Begin by analyzing the prompt.
                Begin by introducing yourself as an AI Advisor, and based on the prompt name, category, and description, explain what your purpose is. Then if the prompt has any variables in it, ask the user for that information, one variable at a time, explaining why you need that input from the user. Once all the variables are collected, return a response for the prompt supplied below.
                Note: If there are no variables, then just return a response for the prompt supplied below.';

                $message->content = $additionalContent."\n\n".$this->content->prompt;
            } else {
                $message->content = $this->content->prompt;
            }

            $use = $this->content->uses()->make();
            $use->user()->associate($this->thread->user);
            $use->save();
        } else {
            $message->content = $this->content;
        }

        $message->request = $this->request;
        $message->thread()->associate($this->thread);
        $message->user()->associate($this->thread->user);

        if ($this->content instanceof Prompt) {
            $message->prompt()->associate($this->content);
        }

        $message->save();

        $aiService = $this->thread->assistant->model->getService();

        $response = new AiMessage;
        $response->thread()->associate($this->thread);
        $response->content = '';

        try {
            $stream = $aiService->sendMessage(
                message: $message,
                files: $this->files,
                hasImageGeneration: $this->hasImageGeneration,
            );
        } catch (Throwable $throwable) {
            if ($this->hasImageGeneration && ($this->attempts() < 3)) {
                $this->release();

                return;
            }

            throw $throwable;
        }

        $chunkBuffer = [];
        $chunkCount = 0;

        $finishChunk = null;

        foreach ($stream() as $chunk) {
            if ($chunk instanceof Meta) {
                $response->message_id = $chunk->messageId;

                continue;
            }

            if ($chunk instanceof Finish) {
                $finishChunk = $chunk;

                continue;
            }

            if ($chunk instanceof Text) {
                $chunkBuffer[] = $chunk->content;
                $chunkCount++;

                if ($chunkCount >= 30) {
                    event(new AdvisorMessageChunk(
                        $this->thread,
                        content: implode('', $chunkBuffer),
                    ));
                    $response->content .= implode('', $chunkBuffer);

                    $chunkBuffer = [];
                    $chunkCount = 0;
                }
            }
        }

        if ($finishChunk->isIncomplete) {
            $chunkBuffer[] = '...';
            $chunkCount++;
        }

        if ($chunkBuffer !== []) {
            event(new AdvisorMessageChunk(
                $this->thread,
                content: implode('', $chunkBuffer),
            ));
            $response->content .= implode('', $chunkBuffer);
        }

        event(new AdvisorMessageFinished(
            $this->thread,
            isIncomplete: $finishChunk->isIncomplete,
            error: $finishChunk->error,
            rateLimitResetsAt: $finishChunk->rateLimitResetsAt,
        ));

        DB::transaction(function () use ($response): void {
            $response->save();
            $this->thread->touch();
        });
    }

    public function tries(): int
    {
        return $this->hasImageGeneration ? 3 : 1;
    }

    public function failed(?Throwable $exception): void
    {
        event(new AdvisorMessageFinished(
            $this->thread,
            error: 'An error happened when sending your message.',
        ));

        report_if($exception instanceof Throwable, $exception);
    }
}
