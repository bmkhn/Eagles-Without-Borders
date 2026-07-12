<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionCRUDTest extends TestCase
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

        $response = $this->get(route('admin.positions.index'));

        $response->assertStatus(200);
        $response->assertSee('Positions');
    }

    /** @test */
    public function create_page_is_accessible(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.positions.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Position');
    }

    /** @test */
    public function it_can_create_a_position(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.positions.store'), [
            'name' => 'New Position',
        ]);

        $response->assertRedirect(route('admin.positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('positions', ['name' => 'New Position']);
    }

    /** @test */
    public function it_validates_required_fields_on_store(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.positions.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_position_name(): void
    {
        Position::factory()->create(['name' => 'Existing Position']);

        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.positions.store'), [
            'name' => 'Existing Position',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function edit_page_is_accessible(): void
    {
        $position = Position::factory()->create(['name' => 'Editable Position']);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.positions.edit', $position));

        $response->assertStatus(200);
        $response->assertSee('Update Position');
        $response->assertSee('Editable Position');
    }

    /** @test */
    public function it_can_update_a_position(): void
    {
        $position = Position::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->superAdmin);

        $response = $this->put(route('admin.positions.update', $position), [
            'name' => 'Updated Position',
        ]);

        $response->assertRedirect(route('admin.positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('positions', ['name' => 'Updated Position']);
    }

    /** @test */
    public function it_logs_activity_on_create(): void
    {
        $this->actingAs($this->superAdmin);

        $this->post(route('admin.positions.store'), [
            'name' => 'Activity Position',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'created',
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function it_can_destroy_position_without_members(): void
    {
        $position = Position::factory()->create(['name' => 'Deletable Position']);

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    /** @test */
    public function it_blocks_destroy_when_position_has_members(): void
    {
        $position = Position::factory()->create(['name' => 'Protected Position']);
        Member::factory()->create(['position_id' => $position->id]);

        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => true,
            'confirm_text' => 'DELETE',
        ]);

        $response->assertRedirect(route('admin.positions.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    /** @test */
    public function it_shows_index_with_existing_positions(): void
    {
        Position::factory()->create(['name' => 'Position A']);
        Position::factory()->create(['name' => 'Position B']);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.positions.index'));

        $response->assertStatus(200);
        $response->assertSee('Position A');
        $response->assertSee('Position B');
    }
}
