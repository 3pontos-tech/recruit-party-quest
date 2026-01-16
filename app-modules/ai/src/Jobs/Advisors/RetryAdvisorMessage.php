<?php

declare(strict_types=1);

namespace He4rt\Ai\Jobs\Advisors;

use He4rt\Ai\Events\Advisors\AdvisorMessageChunk;
use He4rt\Ai\Events\Advisors\AdvisorMessageFinished;
use He4rt\Ai\Models\AiMessage;
use He4rt\Ai\Models\AiMessageFile;
use He4rt\Ai\Models\AiThread;
use He4rt\Ai\Support\StreamingChunks\Finish;
use He4rt\Ai\Support\StreamingChunks\Image;
use He4rt\Ai\Support\StreamingChunks\Meta;
use He4rt\Ai\Support\StreamingChunks\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

final class RetryAdvisorMessage implements ShouldQueue
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
        private string $content,
        private array $request = [],
        private array $files = [],
        private bool $hasImageGeneration = false,
    ) {}

    public function handle(): void
    {
        $message = $this->thread->messages()->whereBelongsTo($this->thread->user)->latest()->first();

        if ($message?->content !== $this->content) {
            $message = new AiMessage;
            $message->content = $this->content;
            $message->thread()->associate($this->thread);
            $message->user()->associate($this->thread->user);
        }

        $message->request = $this->request;
        $message->thread()->associate($this->thread);
        $message->user()->associate($this->thread->user);

        $message->save();

        $aiService = $this->thread->assistant->model->getService();

        $response = new AiMessage;
        $response->thread()->associate($this->thread);
        $response->content = '';

        try {
            $stream = $aiService->retryMessage(
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

            if ($chunk instanceof Image) {
                $media = $this->thread->addMediaFromBase64($chunk->content)
                    ->usingFileName(Str::random().'.'.$chunk->format)
                    ->toMediaCollection('generated_images', diskName: 's3-public');

                $chunk = new Text(sprintf('![Generated image](%s)', $media->getUrl()));
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

        $response->save();
        $this->thread->touch();
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
