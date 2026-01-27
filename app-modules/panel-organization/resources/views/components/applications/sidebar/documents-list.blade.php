@props([
    'record' => [],
])

@php
    $candidate = $record->candidate;

    // Mock documents data - in real implementation this would come from database
    $documents = [
        [
            'id' => 1,
            'name' => 'Resume.pdf',
            'type' => 'resume',
            'size' => '2.1 MB',
            'uploaded_at' => now()->subDays(5),
            'icon' => \Filament\Support\Icons\Heroicon::DocumentText,
            'color' => 'primary',
        ],
        [
            'id' => 2,
            'name' => 'Portfolio_Samples.zip',
            'type' => 'portfolio',
            'size' => '15.7 MB',
            'uploaded_at' => now()->subDays(5),
            'icon' => \Filament\Support\Icons\Heroicon::Folder,
            'color' => 'success',
        ],
        [
            'id' => 3,
            'name' => 'Cover_Letter.pdf',
            'type' => 'cover_letter',
            'size' => '245 KB',
            'uploaded_at' => now()->subDays(5),
            'icon' => \Filament\Support\Icons\Heroicon::Envelope,
            'color' => 'info',
        ],
        [
            'id' => 4,
            'name' => 'References.pdf',
            'type' => 'references',
            'size' => '1.3 MB',
            'uploaded_at' => now()->subDays(3),
            'icon' => \Filament\Support\Icons\Heroicon::Users,
            'color' => 'warning',
        ],
    ];
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-text-high text-sm font-semibold">{{ __('panel-organization::documents.title') }}</h3>
            <x-he4rt::tag size="sm" variant="outline">
                {{ trans_choice('panel-organization::documents.count', count($documents), ['count' => count($documents)]) }}
            </x-he4rt::tag>
        </div>
        <x-he4rt::icon
            :icon="\Filament\Support\Icons\Heroicon::DocumentDuplicate"
            size="sm"
            class="text-icon-medium"
        />
    </div>

    {{-- Documents List --}}
    <div class="space-y-2">
        @foreach ($documents as $document)
            <div
                class="bg-elevation-02dp border-outline-low hover:bg-elevation-03dp rounded-md border p-3 transition-colors"
            >
                <div class="flex items-center gap-3">
                    {{-- Document Icon --}}
                    <div
                        class="bg-{{ $document['color'] }}-100 text-{{ $document['color'] }}-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                    >
                        <x-he4rt::icon :icon="{{ $document['icon'] }}" size="sm" />
                    </div>

                    {{-- Document Info --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-text-high truncate text-sm font-medium">{{ $document['name'] }}</p>
                        <div class="text-text-medium flex items-center gap-2 text-xs">
                            <span>{{ $document['size'] }}</span>
                            <span>•</span>
                            <span>{{ $document['uploaded_at']->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1">
                        <x-he4rt::button
                            size="xs"
                            variant="outline"
                            :icon="\Filament\Support\Icons\Heroicon::Eye"
                            disabled
                            title="{{ __('panel-organization::documents.view_title') }}"
                        />
                        <x-he4rt::button
                            size="xs"
                            variant="outline"
                            :icon="\Filament\Support\Icons\Heroicon::ArrowDownTray"
                            disabled
                            title="{{ __('panel-organization::documents.download_title') }}"
                        />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Upload New Document --}}
    <div class="border-outline-low border-t pt-3">
        <x-he4rt::button
            size="sm"
            variant="outline"
            class="w-full"
            :icon="\Filament\Support\Icons\Heroicon::Plus"
            disabled
        >
            {{ __('panel-organization::documents.upload_button') }}
        </x-he4rt::button>
    </div>

    {{-- Document Stats --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
        <div class="grid grid-cols-2 gap-4 text-center">
            <div>
                <p class="text-text-high text-sm font-bold">{{ count($documents) }}</p>
                <p class="text-text-medium text-xs">{{ __('panel-organization::documents.total_files') }}</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">19.3 MB</p>
                <p class="text-text-medium text-xs">{{ __('panel-organization::documents.total_size') }}</p>
            </div>
        </div>
    </div>

    {{-- Document Types Guide --}}
    <div class="space-y-2">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">
            {{ __('panel-organization::documents.types_title') }}
        </h4>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="flex items-center gap-2">
                <div class="bg-primary-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">{{ __('panel-organization::documents.type.resume') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-success-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">{{ __('panel-organization::documents.type.portfolio') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-info-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">{{ __('panel-organization::documents.type.cover_letter') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-warning-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">{{ __('panel-organization::documents.type.references') }}</span>
            </div>
        </div>
    </div>
</div>
