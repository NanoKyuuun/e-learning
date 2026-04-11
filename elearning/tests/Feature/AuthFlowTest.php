<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup roles
    Role::create(['name' => 'admin-sistem']);
    Role::create(['name' => 'kajur']);
    Role::create(['name' => 'guru']);
    Role::create(['name' => 'siswa']);
});

test('login page is accessible', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-sistem');

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('guru cannot access admin dashboard', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $response = $this->actingAs($guru)->get('/admin/dashboard');
    $response->assertStatus(403);
});

test('guest is redirected to login', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
});
