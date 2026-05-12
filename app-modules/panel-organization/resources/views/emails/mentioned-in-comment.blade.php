@component('mail::message')
    # {{ __('panel-organization::filament.emails.mention.subject', ['author' => $comment->author->name]) }}

    {{ __('panel-organization::filament.emails.mention.greeting', ['name' => $mentionedUser->name]) }}

    {{ __('panel-organization::filament.emails.mention.intro', ['author' => $comment->author->name, 'candidate' => $candidateName]) }}

    > "{{ strip_tags($comment->body) }}" > — {{ $comment->author->name }} · {{ $comment->created_at }}

    @component('mail::button', ['url' => $url])
        {{ __('panel-organization::filament.emails.mention.button') }}
    @endcomponent

    {{ __('panel-organization::filament.emails.mention.footer') }}
@endcomponent
