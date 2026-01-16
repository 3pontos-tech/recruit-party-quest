<?php

declare(strict_types=1);

namespace He4rt\Ai\Providers;

use Filament\Panel;
use He4rt\Ai\AiPlugin;
use He4rt\Ai\Models\AiAssistant;
use He4rt\Ai\Models\AiMessage;
use He4rt\Ai\Models\AiThread;
use He4rt\Ai\Models\AiThreadFolder;
use He4rt\Ai\Models\Prompt;
use He4rt\Ai\Models\PromptType;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

final class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(fn (Panel $panel) => $panel->plugin(new AiPlugin));
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'ai');

        Relation::morphMap([
            'ai_assistant' => AiAssistant::class,
            'ai_message' => AiMessage::class,
            'ai_thread_folder' => AiThreadFolder::class,
            'ai_thread' => AiThread::class,
            'prompt_type' => PromptType::class,
            'prompt' => Prompt::class,
        ]);

        $this->mergeConfigFrom(__DIR__.'/../../config/ai.php', 'ai');
    }
}
