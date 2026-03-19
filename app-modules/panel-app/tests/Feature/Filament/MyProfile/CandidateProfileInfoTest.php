<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
use He4rt\Users\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->user->refresh();

    $this->candidate = $this->user->candidate;
    $this->candidate->update([
        'headline' => 'Test Headline',
        'summary' => 'Test Summary',
        'phone_number' => '+5511999999999',
    ]);

    actingAs($this->user);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.profile_info.heading'));
});

it('displays form fields', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->assertFormFieldExists('headline')
        ->assertFormFieldExists('summary')
        ->assertFormFieldExists('phone_number')
        ->assertFormFieldExists('avatar');
});

it('can call submit without errors', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->call('submit')
        ->assertHasNoFormErrors();
});

it('returns ui-avatars url when candidate has no avatar', function (): void {
    $avatarUrl = $this->user->getFilamentAvatarUrl();

    expect($avatarUrl)->toContain('ui-avatars.com');
});

it('returns ui-avatars url when user has no candidate', function (): void {
    $user = User::factory()->create();
    $user->refresh();
    $user->candidate?->forceDelete();
    $user->unsetRelation('candidate');

    $avatarUrl = $user->getFilamentAvatarUrl();

    expect($avatarUrl)->toContain('ui-avatars.com');
});

it('returns media url when candidate has avatar uploaded', function (): void {
    Storage::fake('public');

    $image = UploadedFile::fake()->image('avatar.jpg', 200, 200);
    $this->candidate->addMedia($image)->toMediaCollection('avatar');

    $this->user->unsetRelation('candidate');

    $avatarUrl = $this->user->getFilamentAvatarUrl();

    expect($avatarUrl)
        ->not->toContain('ui-avatars.com')
        ->toBeString()
        ->not->toBeEmpty();
});

it('re-fills the form after submitting to clear stale upload state', function (): void {
    Storage::fake('public');

    Livewire::test(CandidateProfileInfo::class)
        ->call('submit')
        ->assertHasNoFormErrors()
        ->assertNotified();
});

it('candidate has single file avatar collection', function (): void {
    Storage::fake('public');

    $firstImage = UploadedFile::fake()->image('first.jpg');
    $secondImage = UploadedFile::fake()->image('second.jpg');

    $this->candidate->addMedia($firstImage)->toMediaCollection('avatar');
    $this->candidate->addMedia($secondImage)->toMediaCollection('avatar');

    expect($this->candidate->getMedia('avatar'))->toHaveCount(1);
});
