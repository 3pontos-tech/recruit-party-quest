<?php

declare(strict_types=1);

test('the application returns a successful response')
    ->get('/')
    ->assertRedirectToRoute('filament.admin.auth.login');
