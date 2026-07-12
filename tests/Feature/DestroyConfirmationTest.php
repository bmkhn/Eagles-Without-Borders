<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Position;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyConfirmationTest extends TestCase
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

        $this->actingAs($this->superAdmin);
    }

    // ─────────────────────────────────────────────
    //  PAYMENT DESTROY
    // ─────────────────────────────────────────────

    /** @test */
    public function payment_destroy_succeeds_with_valid_confirmation(): void
    {
        $member = Member::factory()->create(['status' => 'active']);
        $payment = Payment::factory()->create([
            'member_id' => $member->id,
            'year_paid' => (int) now()->year,
        ]);

        $response = $this->delete(route('admin.payments.destroy', $payment), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.edit', $member));

        // Payment should be soft-deleted
        $this->assertSoftDeleted($payment);

        // Member status should be recalculated (no longer has current year payment)
        $member->refresh();
        $this->assertSame('inactive', $member->status);
    }

    /** @test */
    public function payment_destroy_fails_without_confirm_delete(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->delete(route('admin.payments.destroy', $payment), [
            'confirm_text' => 'DELETE',
        ]);

        $response->assertInvalid(['confirm_delete']);
        $this->assertNotSoftDeleted($payment);
    }

    /** @test */
    public function payment_destroy_fails_without_confirm_text(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->delete(route('admin.payments.destroy', $payment), [
            'confirm_delete' => '1',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertNotSoftDeleted($payment);
    }

    /** @test */
    public function payment_destroy_fails_with_wrong_confirm_text(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->delete(route('admin.payments.destroy', $payment), [
            'confirm_delete' => '1',
            'confirm_text' => 'WRONG',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertNotSoftDeleted($payment);
    }

    /** @test */
    public function payment_destroy_logs_activity(): void
    {
        $member = Member::factory()->create();
        $payment = Payment::factory()->create([
            'member_id' => $member->id,
        ]);

        $this->delete(route('admin.payments.destroy', $payment), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'payment',
            'description' => 'payment_deleted',
            'subject_id' => $payment->id,
            'subject_type' => Payment::class,
            'causer_id' => $this->superAdmin->id,
        ]);
    }

    // ─────────────────────────────────────────────
    //  REGION DESTROY
    // ─────────────────────────────────────────────

    /** @test */
    public function region_destroy_succeeds_with_valid_confirmation(): void
    {
        $region = Region::factory()->create(['name' => 'Empty Region']);

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.regions.index'));

        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    /** @test */
    public function region_destroy_fails_without_confirm_delete(): void
    {
        $region = Region::factory()->create(['name' => 'Test Region']);

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_text' => 'DELETE',
        ]);

        $response->assertInvalid(['confirm_delete']);
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    /** @test */
    public function region_destroy_fails_without_confirm_text(): void
    {
        $region = Region::factory()->create();

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => '1',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    /** @test */
    public function region_destroy_fails_with_wrong_confirm_text(): void
    {
        $region = Region::factory()->create();

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => '1',
            'confirm_text' => 'WRONG',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    /** @test */
    public function region_destroy_fails_when_region_has_clubs(): void
    {
        $region = Region::factory()->create(['name' => 'Region With Clubs']);
        Club::factory()->create([
            'region_id' => $region->id,
            'name' => 'Test Club In Region',
        ]);

        $response = $this->delete(route('admin.regions.destroy', $region), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    // ─────────────────────────────────────────────
    //  CLUB DESTROY
    // ─────────────────────────────────────────────

    /** @test */
    public function club_destroy_succeeds_with_valid_confirmation(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create([
            'region_id' => $region->id,
            'name' => 'Empty Club',
        ]);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.clubs.index'));

        $this->assertDatabaseMissing('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function club_destroy_fails_without_confirm_delete(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_text' => 'DELETE',
        ]);

        $response->assertInvalid(['confirm_delete']);
        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function club_destroy_fails_without_confirm_text(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => '1',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function club_destroy_fails_with_wrong_confirm_text(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => '1',
            'confirm_text' => 'WRONG',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    /** @test */
    public function club_destroy_fails_when_club_has_members(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create([
            'region_id' => $region->id,
            'name' => 'Club With Members',
        ]);

        // Attach a member to the club
        Member::factory()->create(['club_id' => $club->id]);

        $response = $this->delete(route('admin.clubs.destroy', $club), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    // ─────────────────────────────────────────────
    //  POSITION DESTROY
    // ─────────────────────────────────────────────

    /** @test */
    public function position_destroy_succeeds_with_valid_confirmation(): void
    {
        $position = Position::factory()->create(['name' => 'Unused Position']);

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.positions.index'));

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    /** @test */
    public function position_destroy_fails_without_confirm_delete(): void
    {
        $position = Position::factory()->create();

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_text' => 'DELETE',
        ]);

        $response->assertInvalid(['confirm_delete']);
        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    /** @test */
    public function position_destroy_fails_without_confirm_text(): void
    {
        $position = Position::factory()->create();

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => '1',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    /** @test */
    public function position_destroy_fails_with_wrong_confirm_text(): void
    {
        $position = Position::factory()->create();

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => '1',
            'confirm_text' => 'WRONG',
        ]);

        $response->assertInvalid(['confirm_text']);
        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    /** @test */
    public function position_destroy_fails_when_position_has_members(): void
    {
        $position = Position::factory()->create(['name' => 'Position With Members']);
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
        ]);

        $response = $this->delete(route('admin.positions.destroy', $position), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }
}
