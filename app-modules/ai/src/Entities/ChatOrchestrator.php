<?php

declare(strict_types=1);

namespace He4rt\Ai\Entities;

use Exception;
use He4rt\Ai\Contracts\HasGuideline;
use He4rt\Ai\Contracts\HasSteps;
use He4rt\Ai\Contracts\ImplementSchema;
use He4rt\Ai\Contracts\InteractsWithStep;
use He4rt\Ai\Schema\AiSessionSchema;
use Illuminate\Support\Collection;
use Prism\Prism\Contracts\Message;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

use function Laravel\Prompts\info;

final class ChatOrchestrator
{
    public function __construct(
        public Collection $messages,
        public ChatSession $session,
        public ChatTaskManager $taskManager,
        public HasSteps&HasGuideline $assistant,
        public ImplementSchema&InteractsWithStep $step,
        public int $stepIndex = 0,
    ) {}

    public static function from(HasSteps&HasGuideline $assistant): self
    {

        $step = $assistant->steps()
            ->orderBy('order')
            ->first();

        throw_if(! $step instanceof InteractsWithStep || ! $step instanceof ImplementSchema, Exception::class, 'The assistant must implement the InteractsWithStep interface');

        $tasks = collect($step->toSchema()->toArray()['properties'])
            ->mapWithKeys(fn ($value, $key) => [$key => null])
            ->all();

        return new self(
            messages: collect(),
            session: ChatSession::default(),
            taskManager: ChatTaskManager::make($tasks),
            assistant: $assistant,
            step: $step,
            stepIndex: $step->getStepOrder()
        );
    }

    public function taskManager(ChatTaskManager $taskManager): static
    {
        $this->taskManager = $taskManager;

        return $this;
    }

    public function chatSession(ChatSession $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function build(string $userMessage): array
    {
        $this->messages->push([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        return [
            'messages' => $this->prepareNextPromptMessages($userMessage),
            'schema' => $this->buildSchemaWithStep(),
        ];
    }

    public function processResponse(array $structured): static
    {
        $this
            ->chatSession(ChatSession::make($structured['session']))
            ->taskManager(ChatTaskManager::make($structured['step']));

        $this->messages->push([
            'role' => 'assistant',
            'content' => $this->session->assistantResponse,
        ]);

        if ($this->taskManager->isTaskComplete()) {
            info('Task completed! Moving to the next step.');
            $this->stepIndex++;

            $this->updateStepContent();
        }

        return $this;
    }

    /**
     *  System prompt[2]: Main and the current task
     *  Assistant prompt[1]: The latest status of the assistant normalize
     *  User prompt[1]: The current answer step
     *
     * @return Message[]
     */
    private function prepareNextPromptMessages(string $userMessage): array
    {
        return [
            new SystemMessage($this->buildSystemPrompt()),
            new SystemMessage($this->buildSystemTaskPrompt()),
            new AssistantMessage($this->buildAgentUpdatedPrompt()),
            new UserMessage($userMessage),
        ];
    }

    private function buildSchemaWithStep(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'RequestResponse',
            description: 'Base Schema Response for the Diagnose Agent',
            properties: [
                AiSessionSchema::make(),
                $this->step->toSchema(),
            ],
            requiredFields: ['session', 'step']
        );
    }

    private function buildSystemPrompt(): string
    {
        return (string) view(
            'diagnose::prompts.diagnose-chat', [
                'persona' => $this->assistant->getGuideline(),
                'engagement' => file_get_contents(resource_path('prompts/rules/engajamento.md')),
                'security' => file_get_contents(resource_path('prompts/rules/seguranca.md')),
            ]
        );
    }

    private function buildSystemTaskPrompt(): string
    {
        /** @var Collection<int,HasGuideline> $response */
        $response = $this->assistant->steps()
            ->whereIn('order', [$this->stepIndex, $this->stepIndex + 1])
            ->orderBy('order')
            ->get();

        if ($response->isEmpty()) {
            return '';
        }

        /** @var HasGuideline $currentTask */
        $currentTask = $response->first();
        if (array_key_exists(1, $response->toArray())) {
            $nextTask = $response->get(1);
        }

        return (string) view('diagnose::prompts.diagnose-task', [
            'task' => $currentTask,
            'nextTask' => $nextTask ?? null,
        ]);
    }

    private function buildAgentUpdatedPrompt(): string
    {
        return (string) view('diagnose::prompts.diagnose-progress', [
            'mood' => $this->session,
            'progress' => $this->taskManager->getGuideline(),
            'messages' => $this->messages,
        ]);
    }

    private function updateStepContent(): void
    {
        $this->step = $this->assistant->steps()
            ->where('order', $this->stepIndex)
            ->first();

        $tasks = collect($this->step->toSchema()->toArray()['properties'])
            ->mapWithKeys(fn ($value, $key) => [$key => null])
            ->all();

        $this->taskManager(ChatTaskManager::make($tasks));
    }
}
