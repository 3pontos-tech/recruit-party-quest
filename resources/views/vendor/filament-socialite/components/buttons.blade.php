<div
    x-data="{}"
>
    <div class="flex flex-col gap-y-6">
        @if ($messageBag->isNotEmpty())
            @foreach($messageBag->all() as $value)
                <p class="fi-fo-field-wrp-error-message text-danger-600 dark:text-danger-400">{{ __($value) }}</p>
            @endforeach
        @endif

        @if (count($visibleProviders))
            <x-he4rt::text class="text-center">{{ __('filament-socialite::auth.login-via') }}:</x-he4rt::text>

            <div class="hidden md:grid @if(count($visibleProviders) > 1) grid-cols-2 @endif gap-4">
                @foreach($visibleProviders as $key => $provider)
                    <x-he4rt::button
                        variant="outline"
                        :icon="$provider->getIcon()"
                        tag="a"
                        :href="route($socialiteRoute, $key)"
                        :spa-mode="false"
                    >
                        {{ $provider->getLabel() }}
                    </x-he4rt::button>
                @endforeach
            </div>

            <div class="flex sm:hidden items-center justify-center gap-8">
                @foreach($visibleProviders as $key => $provider)
                    <x-he4rt::button
                        variant="outline"
                        :icon="$provider->getIcon()"
                        tag="a"
                        :href="route($socialiteRoute, $key)"
                        :spa-mode="false"
                        size="lg"
                    />
                @endforeach
            </div>
        @else
            <span></span>
        @endif
    </div>
</div>
