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
            'icon' => 'heroicon-o-document-text',
            'color' => 'primary',
        ],
        [
            'id' => 2,
            'name' => 'Portfolio_Samples.zip',
            'type' => 'portfolio',
            'size' => '15.7 MB',
            'uploaded_at' => now()->subDays(5),
            'icon' => 'heroicon-o-folder',
            'color' => 'success',
        ],
        [
            'id' => 3,
            'name' => 'Cover_Letter.pdf',
            'type' => 'cover_letter',
            'size' => '245 KB',
            'uploaded_at' => now()->subDays(5),
            'icon' => 'heroicon-o-envelope',
            'color' => 'info',
        ],
        [
            'id' => 4,
            'name' => 'References.pdf',
            'type' => 'references',
            'size' => '1.3 MB',
            'uploaded_at' => now()->subDays(3),
            'icon' => 'heroicon-o-users',
            'color' => 'warning',
        ],
    ];
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-text-high text-sm font-semibold">Documents</h3>
            <x-filament::badge size="sm" color="gray">
                {{ count($documents) }}
            </x-filament::badge>
        </div>
        <x-he4rt::icon icon="heroicon-o-document-duplicate" size="sm" class="text-icon-medium" />
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
                        <x-he4rt::icon icon="{{ $document['icon'] }}" size="sm" />
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
                        <x-filament::button
                            size="xs"
                            color="gray"
                            outlined
                            icon="heroicon-o-eye"
                            disabled
                            title="View document"
                        />
                        <x-filament::button
                            size="xs"
                            color="gray"
                            outlined
                            icon="heroicon-o-arrow-down-tray"
                            disabled
                            title="Download document"
                        />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Upload New Document --}}
    <div class="border-outline-low border-t pt-3">
        <x-filament::button size="sm" color="primary" outlined class="w-full" icon="heroicon-o-plus" disabled>
            Upload Additional Document
        </x-filament::button>
    </div>

    {{-- Document Stats --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
        <div class="grid grid-cols-2 gap-4 text-center">
            <div>
                <p class="text-text-high text-sm font-bold">{{ count($documents) }}</p>
                <p class="text-text-medium text-xs">Total Files</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">19.3 MB</p>
                <p class="text-text-medium text-xs">Total Size</p>
            </div>
        </div>
    </div>

    {{-- Document Types Guide --}}
    <div class="space-y-2">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">Document Types</h4>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="flex items-center gap-2">
                <div class="bg-primary-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">Resume/CV</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-success-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">Portfolio</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-info-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">Cover Letter</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-warning-500 h-2 w-2 rounded-full"></div>
                <span class="text-text-medium">References</span>
            </div>
        </div>
    </div>
</div>
