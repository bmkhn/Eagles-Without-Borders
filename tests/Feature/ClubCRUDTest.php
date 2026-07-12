<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Region $region;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->region = Region::factory()->create(['name' => 'Test Region']);
    }

    /** @test */
    public function index_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.clubs.index'));

        $response->assertStatus(200);
        $response->assertSee('Clubs');
    }

    /** @test */
    public function create_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.clubs.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Club');
        $response->assertSee('Test Region');
    }

    /** @test */
    public function it_can_create_a_club_with_club_president(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.clubs.store'), [
            'region_id' => $this->region->id,
            'name' => 'Test Club',
            'cp_name' => 'Club President',
            'cp_email' => 'president@test.com',
            'cp_password' => 'password123',
            'cp_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.clubs.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clubs', ['name' => 'Test Club']);
        $this->assertDatabaseHas('users', [
            'name' => 'Club President',
            'email' => 'president@test.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_store(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.clubs.store'), []);

        $response->assertSessionHasErrors(['region_id', 'name', 'cp_name', 'cp_email', 'cp_password']);
    }

    /** @test */
    public function edit_page_is_accessible(): void
    {
        $club = Club::factory()->create(['region_id' => $this->region->id]);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.clubs.edit', $club));

        $response->assertStatus(200);
        $response->assertSee('Update Club');
        $response->assertSee($club->name);
    }

    /** @test */
    public function it_can_update_a_club(): void
    {
        $club = Club::factory()->create([
            'region_id' => $this->region->id,
            'name' => 'Old Club Name',
        ]);

        $this->actingAs($this->superAdmin);

        $response = $this->put(route('admin.clubs.update', $club), [
            'region_id' => $this->region->id,
            'name' => 'Updated Club',
        ]);

        $response->assertRedirect(route('admin.clubs.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clubs', ['name' => 'Updated Club']);
    }

    /** @test */
    public function it_logs_activity_on_create(): void
    {
        $this->actingAs($this->superAdmin);

        $this->post(route('admin.clubs.store'), [
            'region_id' => $this->region->id,
            'name' => 'Activity Club',
            'cp_name' => 'Activity Pres',
            'cp_email' => 'activity@test.com',
            'cp_password' => 'password123',
            'cp_password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'created',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_can_destroy_club_without_members(): void
    {
        $club = Club::factory()->create(['region_id' => $this->region->id]);

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.clubs.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function it_blocks_destroy_when_club_has_members(): void
    {
        $club = Club::factory()->create(['region_id' => $this->region->id]);
        Member::factory()->create(['club_id' => $club->id]);

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.clubs.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function it_shows_index_with_existing_clubs(): void
    {
        Club::factory()->create(['region_id' => $this->region->id, 'name' => 'Club One']);
        Club::factory()->create(['region_id' => $this->region->id, 'name' => 'Club Two']);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.clubs.index'));

        $response->assertStatus(200);
        $response->assertSee('Club One');
        $response->assertSee('Club Two');
    }
}
