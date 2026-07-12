<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
    }

    /** @test */
    public function index_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.admins.index'));

        $response->assertStatus(200);
        $response->assertSee('Admin Accounts');
    }

    /** @test */
    public function index_page_lists_admin_accounts(): void
    {
        $admin = User::factory()->create(['name' => 'John Admin']);
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.admins.index'));

        $response->assertStatus(200);
        $response->assertSee('John Admin');
    }

    /** @test */
    public function create_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.admins.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Admin');
    }

    /** @test */
    public function it_can_create_a_national_admin(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.admins.store'), [
            'name' => 'New National Admin',
            'email' => 'national@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'national-admin',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New National Admin',
            'email' => 'national@test.com',
        ]);
    }

    /** @test */
    public function it_can_create_a_regional_admin(): void
    {
        $region = Region::factory()->create();

        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.admins.store'), [
            'name' => 'New Regional Admin',
            'email' => 'regional@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'regional-admin',
            'region_id' => $region->id,
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New Regional Admin',
            'email' => 'regional@test.com',
        ]);
    }

    /** @test */
    public function it_can_create_a_club_admin(): void
    {
        $club = Club::factory()->create();

        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.admins.store'), [
            'name' => 'New Club Admin',
            'email' => 'clubadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'club-admin',
            'club_id' => $club->id,
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New Club Admin',
            'email' => 'clubadmin@test.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_store(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.admins.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    /** @test */
    public function edit_page_is_accessible(): void
    {
        $admin = User::factory()->create(['name' => 'Editable Admin']);
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.admins.edit', $admin));

        $response->assertStatus(200);
        $response->assertSee('Edit Admin');
        $response->assertSee('Editable Admin');
    }

    /** @test */
    public function it_can_update_an_admin(): void
    {
        $admin = User::factory()->create(['name' => 'Old Name']);
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $response = $this->patch(route('admin.admins.update', $admin), [
            'name' => 'Updated Admin',
            'email' => $admin->email,
            'role' => 'national-admin',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['name' => 'Updated Admin']);
    }

    /** @test */
    public function it_can_update_admin_password(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $response = $this->patch(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'national-admin',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_logs_activity_on_create(): void
    {
        $this->actingAs($this->superAdmin);

        $this->post(route('admin.admins.store'), [
            'name' => 'Activity Admin',
            'email' => 'activity@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'national-admin',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'created_admin',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_logs_activity_on_update(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $this->patch(route('admin.admins.update', $admin), [
            'name' => 'Updated Name',
            'email' => $admin->email,
            'role' => 'national-admin',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'updated_admin',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_can_destroy_an_admin(): void
    {
        $admin = User::factory()->create(['name' => 'Deletable Admin']);
        $admin->assignRole('national-admin');

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.admins.destroy', $admin));

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    /** @test */
    public function it_prevents_self_deletion(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.admins.destroy', $this->superAdmin));

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    /** @test */
    public function it_prevents_non_admin_users_from_accessing(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser);

        $response = $this->get(route('admin.admins.index'));

        $response->assertStatus(403);
    }
}
