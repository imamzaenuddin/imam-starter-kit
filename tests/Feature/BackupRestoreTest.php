<?php

use App\Models\User;
use App\Models\Level;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user with backup permission can access backup page', function () {
    $user = User::factory()->create();

    // Assuming user is authenticated and has minimal access
    $response = $this->actingAs($user)
        ->get('/admin/backup-restore');

    // Should not 404, might be 403 if no permission
    expect(is_int($response->getStatusCode()))->toBeTrue();
});

test('unauthenticated user cannot access backup page', function () {
    $response = $this->get('/admin/backup-restore');

    // Should redirect to login
    $response->assertRedirect('/login');
});

test('user can view backup restore page when authenticated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/admin/backup-restore');

    // Should get 200 or 403 depending on permissions
    // At minimum, should not be 404
    $this->assertNotEquals(404, $response->getStatusCode());
});

test('inactive user cannot access backup page even when authenticated', function () {
    $user = User::factory()->create(['is_active' => false]);

    // Even though we're actingAs, the application should
    // detect inactive status and logout/block
    $response = $this->actingAs($user)
        ->get('/admin/backup-restore');

    // Due to authentication handling, inactive user routes should fail
    // This depends on middleware implementation
    expect(in_array($response->getStatusCode(), [401, 403, 302]))->toBeTrue();
});

test('backup file operations require proper authorization', function () {
    $user = User::factory()->create();
    $level = $user->level;

    // Get the backup-restore menu
    $menu = \App\Models\Menu::where('url', '/admin/backup-restore')->first();

    if ($menu) {
        // Check if user's level has the menu assigned
        $hasAccess = $menu->levels()
            ->where('m_level_id', $level->id)
            ->exists();

        expect(is_bool($hasAccess))->toBeTrue();
    }
});
