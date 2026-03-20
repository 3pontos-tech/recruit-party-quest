<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use He4rt\Users\Listeners\SaveGitHubTokenListener;
use He4rt\Users\Models\SocialAccount;
use He4rt\Users\User;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

function makeFakeOAuthUser(string $id = '12345', string $nickname = 'johndoe', string $token = 'fake-token'): SocialiteUserContract
{
    return new class($id, $nickname, $token) implements SocialiteUserContract
    {
        public function __construct(private readonly string $githubId, private readonly string $nickname, public string $token) {}

        public function getId(): string
        {
            return $this->githubId;
        }

        public function getNickname(): string
        {
            return $this->nickname;
        }

        public function getName(): string
        {
            return $this->nickname;
        }

        public function getEmail(): string
        {
            return 'user@example.com';
        }

        public function getAvatar(): string
        {
            return '';
        }
    };
}

it('updates access token when a login event fires', function (): void {
    $user = User::factory()->create();
    $socialAccount = SocialAccount::factory()->github()->create([
        'user_id' => $user->id,
        'provider_id' => '12345',
        'access_token' => 'old-token',
    ]);
    $socialAccount->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('12345', 'johndoe', 'new-access-token');

    $event = new Login($socialAccount, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    expect($socialAccount->fresh()->access_token)->toBe('new-access-token');
});

it('updates access token on re-login', function (): void {
    $user = User::factory()->create();
    $socialAccount = SocialAccount::factory()->github()->create([
        'user_id' => $user->id,
        'provider_id' => '12345',
        'access_token' => 'old-token',
    ]);
    $socialAccount->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('12345', 'new-username', 'refreshed-token');

    $event = new Login($socialAccount, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    expect($socialAccount->fresh()->access_token)->toBe('refreshed-token');
});

it('does nothing for non-github providers', function (): void {
    $user = User::factory()->create();
    $socialAccount = SocialAccount::factory()->google()->create([
        'user_id' => $user->id,
        'provider_id' => '99999',
        'access_token' => 'original-token',
    ]);
    $socialAccount->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('99999', 'googleuser', 'google-token');

    $event = new Login($socialAccount, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    expect($socialAccount->fresh()->access_token)->toBe('original-token');
});

it('updates access token when a registered event fires', function (): void {
    $user = User::factory()->create();
    $socialAccount = SocialAccount::factory()->github()->create([
        'user_id' => $user->id,
        'provider_id' => '77777',
        'access_token' => null,
    ]);
    $socialAccount->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('77777', 'newuser', 'reg-token');

    $event = new Registered('github', $oauthUser, $socialAccount);
    new SaveGitHubTokenListener()->handle($event);

    expect($socialAccount->fresh()->access_token)->toBe('reg-token');
});
