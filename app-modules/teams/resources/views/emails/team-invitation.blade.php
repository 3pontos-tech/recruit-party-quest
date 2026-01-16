@component('mail::message')
    # {{ __('teams::filament.emails.team_invitation.subject', ['team_name' => $team->name]) }}

    {{ __('teams::filament.emails.team_invitation.greeting', ['name' => $user->name]) }}

    {{ __('teams::filament.emails.team_invitation.introduction', ['team_name' => $team->name]) }}

    ## {{ __('teams::filament.emails.team_invitation.credentials_title') }} -
    **{{ __('teams::filament.emails.team_invitation.email_label') }}:** {{ $user->email }} -
    **{{ __('teams::filament.emails.team_invitation.password_label') }}:**
    {{ __('teams::filament.emails.team_invitation.password_recovery') }}

    @component('mail::button', ['url' => config('app.url') . '/login'])
        {{ __('teams::filament.emails.team_invitation.login_button') }}
    @endcomponent

    > {{ __('teams::filament.emails.team_invitation.instructions') }}

    @component('mail::button', ['url' => config('app.url') . '/forgot-password'])
        {{ __('teams::filament.emails.team_invitation.forgot_password_button') }}
    @endcomponent

    {{ __('teams::filament.emails.team_invitation.footer', ['team_name' => $team->name]) }}
@endcomponent
