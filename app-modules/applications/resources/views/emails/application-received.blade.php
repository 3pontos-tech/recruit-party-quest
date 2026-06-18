<x-mail::message>
# {{ __('applications::filament.emails.application_received.greeting', ['name' => $candidateName]) }}

{{ __('applications::filament.emails.application_received.line', ['job' => $jobTitle]) }}

<x-mail::button :url="$url">
{{ __('applications::filament.emails.application_received.action') }}
</x-mail::button>
</x-mail::message>
