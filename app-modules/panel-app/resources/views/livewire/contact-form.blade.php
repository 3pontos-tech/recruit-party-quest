<div>
    @if ($isSent)
        <div
            class="bg-elevation-surface/20 border-outline-light dark:border-outline-dark flex flex-1 flex-col items-center justify-center gap-6 rounded-lg border p-8 text-center"
        >
            <div
                class="bg-success-light/10 dark:bg-success-dark/10 flex h-16 w-16 items-center justify-center rounded-full"
            >
                <x-heroicon-o-check-circle class="text-success-light dark:text-success-dark h-8 w-8" />
            </div>
            <div class="flex flex-col gap-2">
                <x-he4rt::heading level="3" size="sm">Mensagem enviada com sucesso!</x-he4rt::heading>
                <x-he4rt::text class="text-text-medium">Em breve entraremos em contato.</x-he4rt::text>
            </div>
            <x-he4rt::button variant="outline" wire:click="resetForm">Enviar outra mensagem</x-he4rt::button>
        </div>
    @else
        <form
            wire:submit.prevent="submit"
            class="bg-elevation-surface/20 border-outline-light dark:border-outline-dark flex flex-1 flex-col gap-8 rounded-lg border p-4 lg:p-6"
        >
            {{-- Honeypot: must remain hidden and empty --}}
            <div style="display: none" aria-hidden="true">
                <input type="text" name="contact_url" wire:model="honeypot" tabindex="-1" autocomplete="off" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1">
                    <x-he4rt::input
                        label="Nome completo"
                        placeholder="Digite seu nome completo"
                        name="name"
                        wire:model="name"
                    />
                    @error('name')
                        <x-he4rt::text size="xs" class="text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </x-he4rt::text>
                    @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <x-he4rt::input
                        label="Email"
                        placeholder="Digite seu email"
                        name="email"
                        type="email"
                        wire:model="email"
                    />
                    @error('email')
                        <x-he4rt::text size="xs" class="text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </x-he4rt::text>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <x-he4rt::input label="Telefone" placeholder="(99) 99999-9999" name="phone" wire:model="phone" />
                @error('phone')
                    <x-he4rt::text size="xs" class="text-danger-600 dark:text-danger-400">
                        {{ $message }}
                    </x-he4rt::text>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <x-he4rt::textarea
                    label="Mensagem"
                    placeholder="Digite sua mensagem..."
                    name="message"
                    wire:model="message"
                />
                @error('message')
                    <x-he4rt::text size="xs" class="text-danger-600 dark:text-danger-400">
                        {{ $message }}
                    </x-he4rt::text>
                @enderror
            </div>

            @if ($hasError)
                <div
                    class="bg-danger-50 dark:bg-danger-950/20 border-danger-200 dark:border-danger-800 rounded-md border p-4"
                >
                    <x-he4rt::text size="sm" class="text-danger-700 dark:text-danger-400">
                        Ocorreu um erro ao enviar sua mensagem. Por favor, tente novamente ou entre em contato
                        diretamente pelo e-mail
                        <strong>recrutamento@3pontos.com</strong>
                        .
                    </x-he4rt::text>
                </div>
            @endif

            @if ($rateLimitedUntil)
                <div
                    class="bg-warning-50 dark:bg-warning-950/20 border-warning-200 dark:border-warning-800 rounded-md border p-4"
                >
                    <x-he4rt::text size="sm" class="text-warning-700 dark:text-warning-400">
                        Muitas tentativas em pouco tempo. {{ $rateLimitedUntil }}
                    </x-he4rt::text>
                </div>
            @endif

            <x-he4rt::button type="submit" :loading="$isLoading" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">Enviar mensagem</span>
                <span wire:loading wire:target="submit">Enviando...</span>
            </x-he4rt::button>
        </form>
    @endif
</div>
