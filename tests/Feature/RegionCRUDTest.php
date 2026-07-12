<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionCRUDTest extends TestCase
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

        $response = $this->get(route('admin.regions.index'));

        $response->assertStatus(200);
        $response->assertSee('Regions');
    }

    /** @test */
    public function create_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.regions.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Region');
    }

    /** @test */
    public function it_can_create_a_region_with_regional_admin(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.regions.store'), [
            'name' => 'Test Region',
            'ra_name' => 'Regional Admin',
            'ra_email' => 'regional@test.com',
            'ra_password' => 'password123',
            'ra_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('regions', ['name' => 'Test Region']);
        $this->assertDatabaseHas('users', [
            'name' => 'Regional Admin',
            'email' => 'regional@test.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_store(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.regions.store'), []);

        $response->assertSessionHasErrors(['name', 'ra_name', 'ra_email', 'ra_password']);
    }

    /** @test */
    public function it_validates_unique_region_name(): void
    {
        Region::factory()->create(['name' => 'Existing Region']);

        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.regions.store'), [
            'name' => 'Existing Region',
            'ra_name' => 'Regional Admin',
            'ra_email' => 'regional@test.com',
            'ra_password' => 'password123',
            'ra_password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function edit_page_is_accessible(): void
    {
        $region = Region::factory()->create();

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.regions.edit', $region));

        $response->assertStatus(200);
        $response->assertSee('Update Region');
        $response->assertSee($region->name);
    }

    /** @test */
    public function it_can_update_a_region(): void
    {
        $region = Region::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->superAdmin);

        $response = $this->put(route('admin.regions.update', $region), [
            'name' => 'Updated Region',
        ]);

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('regions', ['name' => 'Updated Region']);
    }

    /** @test */
    public function it_logs_activity_on_create(): void
    {
        $this->actingAs($this->superAdmin);

        $this->post(route('admin.regions.store'), [
            'name' => 'Log Test Region',
            'ra_name' => 'Log Admin',
            'ra_email' => 'log@test.com',
            'ra_password' => 'password123',
            'ra_password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'created',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_logs_activity_on_update(): void
    {
        $region = Region::factory()->create(['name' => 'Before Update']);

        $this->actingAs($this->superAdmin);

        $this->put(route('admin.regions.update', $region), [
            'name' => 'After Update',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'updated',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_can_destroy_region_without_clubs(): void
    {
        $region = Region::factory()->create();

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    /** @test */
    public function it_blocks_destroy_when_region_has_clubs(): void
    {
        $region = Region::factory()->create();
        Club::factory()->create(['region_id' => $region->id]);

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    /** @test */
    public function it_shows_index_with_existing_regions(): void
    {
        Region::factory()->create(['name' => 'Region Alpha']);
        Region::factory()->create(['name' => 'Region Beta']);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.regions.index'));

        $response->assertStatus(200);
        $response->assertSee('Region Alpha');
        $response->assertSee('Region Beta');
    }
}
