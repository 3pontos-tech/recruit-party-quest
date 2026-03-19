<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use He4rt\Users\Listeners\SaveGitHubTokenListener;
use He4rt\Users\Models\UserGitHubConnection;
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

it('creates a github connection when a login event fires', function (): void {
    $user = User::factory()->create();
    $socialiteUser = SocialiteUser::query()->create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
    ]);
    $socialiteUser->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('12345', 'johndoe', 'the-access-token');

    $event = new Login($socialiteUser, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    $connection = UserGitHubConnection::query()->where('user_id', $user->id)->first();

    expect($connection)->not->toBeNull()
        ->and($connection->github_id)->toBe('12345')
        ->and($connection->github_username)->toBe('johndoe');
});

it('updates an existing connection on re-login', function (): void {
    $user = User::factory()->create();
    UserGitHubConnection::factory()->create([
        'user_id' => $user->id,
        'github_id' => '12345',
        'github_username' => 'old-username',
    ]);

    $socialiteUser = SocialiteUser::query()->create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
    ]);
    $socialiteUser->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('12345', 'new-username', 'new-token');

    $event = new Login($socialiteUser, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    expect(UserGitHubConnection::query()->where('user_id', $user->id)->count())->toBe(1);

    $connection = UserGitHubConnection::query()->where('user_id', $user->id)->first();
    expect($connection->github_username)->toBe('new-username');
});

it('does nothing for non-github providers', function (): void {
    $user = User::factory()->create();
    $socialiteUser = SocialiteUser::query()->create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '99999',
    ]);
    $socialiteUser->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('99999', 'googleuser', 'google-token');

    $event = new Login($socialiteUser, $oauthUser);
    new SaveGitHubTokenListener()->handle($event);

    expect(UserGitHubConnection::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('creates a github connection when a registered event fires', function (): void {
    $user = User::factory()->create();
    $socialiteUser = SocialiteUser::query()->create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '77777',
    ]);
    $socialiteUser->setRelation('user', $user);

    $oauthUser = makeFakeOAuthUser('77777', 'newuser', 'reg-token');

    $event = new Registered('github', $oauthUser, $socialiteUser);
    new SaveGitHubTokenListener()->handle($event);

    expect(UserGitHubConnection::query()->where('user_id', $user->id)->exists())->toBeTrue();
});
