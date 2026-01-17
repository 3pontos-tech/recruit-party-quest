<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    public $candidate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->candidate = Candidate::factory()
            ->for(User::factory()->create(['email_verified_at' => now()]), 'user')
            ->create();
    }

    public function test_redirects_to_onboarding_if_not_onboarded(): void
    {
        $notOnboarded = Candidate::factory()
            ->for(User::factory()->create([
                'email_verified_at' => now(),
            ]), 'user')
            ->create(['is_onboarded' => false]);

        $this->actingAs($notOnboarded->user)
            ->get('/')
            ->assertRedirect('/onboarding');
    }

    public function test_displays_onboarding_wizard_for_incomplete_profile(): void
    {
        $this->actingAs($this->candidate->user)
            ->get('/onboarding')
            ->assertSuccessful()
            ->assertSee('Account & Identity');
    }
}
