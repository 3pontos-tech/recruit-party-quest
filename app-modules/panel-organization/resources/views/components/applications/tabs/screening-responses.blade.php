@php
    $screeningResponses = $record
        ->screeningResponses()
        ->with('question')
        ->get();
    $hasResponses = $screeningResponses->count() > 0;
    $knockoutFailures = $screeningResponses->where('is_knockout_fail', true);
    $totalQuestions = $screeningResponses->count();
    $completedQuestions = $screeningResponses->whereNotNull('response_value')->count();
@endphp

<div class="space-y-6">
    @if ($hasResponses)
        {{-- Screening Summary --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Screening Summary</h3>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>{{ $completedQuestions }}/{{ $totalQuestions }} Questions Answered</span>

                    @if ($knockoutFailures->count() > 0)
                        <span
                            class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
                        >
                            {{ $knockoutFailures->count() }} Knockout
                            {{ $knockoutFailures->count() === 1 ? 'Failure' : 'Failures' }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                        >
                            All Requirements Met
                        </span>
                    @endif
                </div>
            </div>

            {{-- Progress Bar --}}
            @php
                $progressPercentage = $totalQuestions > 0 ? round(($completedQuestions / $totalQuestions) * 100) : 0;
            @endphp

            <div class="h-2 w-full rounded-full bg-gray-200">
                <div
                    class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                    style="width: {{ $progressPercentage }}%"
                ></div>
            </div>
        </div>

        {{-- Screening Questions & Responses --}}
        <div class="space-y-4">
            @foreach ($screeningResponses->sortBy('question.display_order') as $response)
                @php
                    $question = $response->question;
                    $questionType = $question->question_type;
                    $responseValue = $response->response_value;
                    $isKnockout = $question->is_knockout;
                    $isKnockoutFail = $response->is_knockout_fail;
                    $isRequired = $question->is_required;
                    $settings = $question->settings ?? [];
                @endphp

                <div
                    class="{{ $isKnockoutFail ? 'border-red-200 bg-red-50' : '' }} rounded-lg border border-gray-200 bg-white p-6"
                >
                    {{-- Question Header --}}
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center space-x-3">
                                <h4 class="text-base font-medium text-gray-900">{{ $question->question_text }}</h4>
                                @if ($isRequired)
                                    <span class="text-sm text-red-500">*</span>
                                @endif

                                @if ($isKnockout)
                                    <span
                                        class="{{ $isKnockoutFail ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }} inline-flex items-center rounded px-2 py-1 text-xs font-medium"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        Knockout Question
                                    </span>
                                @endif
                            </div>
                            <p class="mb-1 text-sm text-gray-600">
                                Question Type:
                                <span class="font-medium">
                                    {{ ucfirst(str_replace(['_'], [' '], $questionType->value ?? 'unknown')) }}
                                </span>
                            </p>
                        </div>

                        @if ($isKnockoutFail)
                            <div class="flex items-center text-red-600">
                                <svg class="mr-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="text-sm font-medium">Failed</span>
                            </div>
                        @elseif ($responseValue)
                            <div class="flex items-center text-green-600">
                                <svg class="mr-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="text-sm font-medium">Answered</span>
                            </div>
                        @endif
                    </div>

                    {{-- Response Value Display --}}
                    <div class="rounded-md bg-gray-50 p-4">
                        @if (! $responseValue)
                            <div class="text-gray-500 italic">No response provided</div>
                        @else
                            @php
                                $value = $responseValue['value'] ?? null;
                            @endphp

                            @switch($questionType->value ?? '')
                                @case('YesNo')
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Answer:</span>
                                        <span
                                            class="{{ $value === 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium"
                                        >
                                            {{ $value === 'yes' ? 'Yes' : 'No' }}
                                        </span>
                                    </div>

                                    @break
                                @case('Text')
                                    <div>
                                        <span class="mb-2 block text-sm text-gray-600">Response:</span>
                                        <div class="rounded border bg-white p-3 text-sm">
                                            {{ $value }}
                                        </div>
                                        @if (isset($settings['max_length']))
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ strlen($value) }}/{{ $settings['max_length'] }} characters
                                            </div>
                                        @endif
                                    </div>

                                    @break
                                @case('Number')
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Value:</span>
                                        <span class="font-mono text-lg">
                                            {{ $settings['prefix'] ?? '' }}{{ $value }}{{ $settings['suffix'] ?? '' }}
                                        </span>
                                    </div>

                                    @break
                                @case('SingleChoice')
                                    @php
                                        $choices = collect($settings['choices'] ?? []);
                                        $selectedChoice = $choices->firstWhere('value', $value);
                                    @endphp

                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Selection:</span>
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-sm font-medium text-blue-800"
                                        >
                                            {{ $selectedChoice['label'] ?? $value }}
                                        </span>
                                    </div>

                                    @break
                                @case('MultipleChoice')
                                    @php
                                        $choices = collect($settings['choices'] ?? []);
                                        $selectedValues = is_array($value) ? $value : [$value];
                                    @endphp

                                    <div>
                                        <span class="mb-2 block text-sm text-gray-600">
                                            Selections ({{ count($selectedValues) }}):
                                        </span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($selectedValues as $selectedValue)
                                                @php
                                                    $selectedChoice = $choices->firstWhere('value', $selectedValue);
                                                @endphp

                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-sm font-medium text-blue-800"
                                                >
                                                    {{ $selectedChoice['label'] ?? $selectedValue }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    @break
                                @case('FileUpload')
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">File:</span>
                                        <div class="flex items-center space-x-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span class="text-sm font-medium">{{ basename($value) }}</span>
                                        </div>
                                    </div>

                                    @break
                                @default
                                    <div class="text-sm">
                                        <span class="text-gray-600">Raw Value:</span>
                                        <pre class="mt-1 rounded bg-gray-100 p-2 text-xs">
{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre
                                        >
                                    </div>
                            @endswitch
                        @endif
                    </div>

                    {{-- Knockout Criteria Display --}}
                    @if ($isKnockout && $question->knockout_criteria)
                        <div class="mt-4 rounded border border-yellow-200 bg-yellow-50 p-3">
                            <div class="text-sm">
                                <span class="font-medium text-yellow-800">Knockout Criteria:</span>
                                <span class="text-yellow-700">
                                    @php
                                        $criteria = $question->knockout_criteria;
                                        if (isset($criteria['expected'])) {
                                            echo 'Expected: ' . ucfirst($criteria['expected']);
                                        } else {
                                            echo json_encode($criteria);
                                        }
                                    @endphp
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Additional Screening Metadata --}}
        @if ($record->created_at)
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Application submitted: {{ $record->created_at->format('M j, Y \a\t g:i A') }}</span>
                        <span>Responses: {{ $completedQuestions }} of {{ $totalQuestions }}</span>
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="rounded-lg border border-gray-200 bg-white p-12">
            <div class="text-center">
                <div class="mx-auto mb-4 h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-medium text-gray-900">No Screening Questions</h3>
                <p class="mb-4 text-gray-600">
                    This application doesn't have any screening questions or the candidate hasn't provided responses
                    yet.
                </p>
                <p class="text-sm text-gray-500">
                    Screening questions help filter candidates based on specific requirements and qualifications.
                </p>
            </div>
        </div>
    @endif
</div>
