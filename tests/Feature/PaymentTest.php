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

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Member $member;

    protected int $currentYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentYear = (int) now()->year;

        // Seed roles and permissions
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        // Create a super admin user
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        // Set up test data: region → club → position → member
        $region = Region::factory()->create(['name' => 'Test Region']);
        $club = Club::factory()->create([
            'name' => 'Test Club',
            'region_id' => $region->id,
        ]);
        $position = Position::factory()->create(['name' => 'Member']);

        $this->member = Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Test',
            'last_name' => 'Member',
            'status' => 'inactive',
        ]);

        $this->actingAs($this->superAdmin);
    }

    /** @test */
    public function it_creates_a_new_payment_when_no_existing_record(): void
    {
        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'date_paid' => '2026-06-15',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'deleted_at' => null,
        ]);

        // Member status should update to 'active' (paid for current year)
        $this->member->refresh();
        $this->assertSame('active', $this->member->status);
    }

    /** @test */
    public function it_returns_error_for_duplicate_active_payment(): void
    {
        // Create an existing active payment
        Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect();

        // Should still have exactly one payment record
        $this->assertEquals(
            1,
            Payment::where('member_id', $this->member->id)
                ->where('year_paid', $this->currentYear)
                ->count()
        );
    }

    /** @test */
    public function it_restores_soft_deleted_payment_instead_of_creating_duplicate(): void
    {
        // Create and soft-delete a payment
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'date_paid' => '2025-01-01',
        ]);
        $paymentId = $payment->id;
        $payment->delete();

        // Verify it's soft-deleted
        $this->assertNotNull($payment->fresh()->deleted_at);

        // Now "create" a payment for the same member+year — should restore the trashed one
        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'date_paid' => '2026-06-15',
        ]);

        $response->assertSessionHas('success');

        // The original payment should now be restored (deleted_at = null)
        $restored = Payment::withTrashed()->find($paymentId);
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);

        // There should be only ONE payment record (the restored one, not a duplicate)
        $this->assertEquals(
            1,
            Payment::where('member_id', $this->member->id)
                ->where('year_paid', $this->currentYear)
                ->count()
        );

        // Member status should update to 'active'
        $this->member->refresh();
        $this->assertSame('active', $this->member->status);
    }

    /** @test */
    public function it_updates_date_paid_and_clears_deleted_at_when_restoring(): void
    {
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'date_paid' => '2025-01-01',
        ]);
        $paymentId = $payment->id;
        $payment->delete();

        // — Verify it's soft-deleted before restore —
        $this->assertNotNull(Payment::withTrashed()->find($paymentId)->deleted_at);

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
            'date_paid' => '2026-06-15',
        ]);

        $response->assertSessionHas('success');

        $restored = Payment::withTrashed()->find($paymentId);
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at, 'deleted_at should be null after restore');
        $this->assertSame('2026-06-15', $restored->date_paid->format('Y-m-d'));
    }

    /** @test */
    public function it_restores_payment_for_previous_years_too(): void
    {
        $lastYear = $this->currentYear - 1;

        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $lastYear,
            'date_paid' => '2025-01-01',
        ]);
        $paymentId = $payment->id;
        $payment->delete();

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $lastYear,
        ]);

        $response->assertSessionHas('success');

        $restored = Payment::withTrashed()->find($paymentId);
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
    }

    /** @test */
    public function it_blocks_club_admin_from_other_clubs_member(): void
    {
        // Create another club for the club-admin
        $otherClub = Club::factory()->create(['name' => 'Other Club']);

        $clubAdmin = User::factory()->create([
            'club_id' => $otherClub->id,
        ]);
        $clubAdmin->assignRole('club-admin');

        $this->actingAs($clubAdmin);

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_club_admin_to_create_payment_for_own_club_member(): void
    {
        $club = $this->member->club;

        $clubAdmin = User::factory()->create([
            'club_id' => $club->id,
        ]);
        $clubAdmin->assignRole('club-admin');

        $this->actingAs($clubAdmin);

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payments', [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);
    }

    /** @test */
    public function it_blocks_regional_admin_from_other_regions_member(): void
    {
        // Create a different region with a club
        $otherRegion = Region::factory()->create(['name' => 'Other Region']);
        $otherClub = Club::factory()->create([
            'name' => 'Other Region Club',
            'region_id' => $otherRegion->id,
        ]);

        $regionalAdmin = User::factory()->create([
            'region_id' => $otherRegion->id,
        ]);
        $regionalAdmin->assignRole('regional-admin');

        $this->actingAs($regionalAdmin);

        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_logs_restored_payment_activity(): void
    {
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);
        $payment->delete();

        $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'payment',
            'description' => 'payment_restored',
            'subject_id' => $payment->id,
            'subject_type' => Payment::class,
            'causer_id' => $this->superAdmin->id,
            'causer_type' => User::class,
        ]);
    }

    /** @test */
    public function it_maintains_unique_constraint_after_restore(): void
    {
        // Create, delete, restore, then try creating another
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);
        $payment->delete();

        // Restore via store()
        $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        // Try creating another — should error because there's already an active payment
        $response = $this->post(route('admin.payments.store'), [
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response->assertSessionHas('error');

        // Still exactly one record
        $this->assertEquals(
            1,
            Payment::where('member_id', $this->member->id)
                ->where('year_paid', $this->currentYear)
                ->count()
        );
    }

    // ─────────────────────────────────────────────
    //  UPDATE() — withTrashed() uniqueness check
    // ─────────────────────────────────────────────

    /** @test */
    public function it_updates_payment_to_a_new_year(): void
    {
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $newYear = $this->currentYear - 1;

        $response = $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $newYear,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.payments.index'));

        $payment->refresh();
        $this->assertSame($newYear, $payment->year_paid);
    }

    /** @test */
    public function it_allows_updating_to_the_same_year(): void
    {
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $response = $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $this->currentYear,
            'date_paid' => '2026-12-01',
        ]);

        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame($this->currentYear, $payment->year_paid);
        $this->assertSame('2026-12-01', $payment->date_paid->format('Y-m-d'));
    }

    /** @test */
    public function it_blocks_updating_to_a_year_with_an_active_payment(): void
    {
        // Create two different payments for the same member
        $payment1 = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $payment2 = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear - 1,
        ]);

        // Try to update payment2 to the same year as payment1
        $response = $this->put(route('admin.payments.update', $payment2), [
            'year_paid' => $this->currentYear,
        ]);

        $response->assertSessionHas('error');

        // payment2 should remain unchanged
        $payment2->refresh();
        $this->assertSame($this->currentYear - 1, $payment2->year_paid);
    }

    /** @test */
    public function it_blocks_updating_to_a_year_with_a_trashed_payment(): void
    {
        // Create an active payment and a soft-deleted one for different years
        $activePayment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        // Create a payment and soft-delete it
        $trashedPayment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear - 1,
        ]);
        $trashedPayment->delete();

        // Try to update the ACTIVE payment to the trashed payment's year
        $response = $this->put(route('admin.payments.update', $activePayment), [
            'year_paid' => $this->currentYear - 1,
        ]);

        $response->assertSessionHas('error');

        // Active payment should remain unchanged
        $activePayment->refresh();
        $this->assertSame($this->currentYear, $activePayment->year_paid);
    }

    /** @test */
    public function it_allows_updating_to_freed_up_year_after_trashed_record_is_removed(): void
    {
        // Create a payment for current year
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        // Create and soft-delete a payment for a different year
        $otherPayment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear - 1,
        ]);
        $otherPaymentId = $otherPayment->id;
        $otherPayment->delete();

        // Try to update main payment to the trashed year — should be blocked
        $response = $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $this->currentYear - 1,
        ]);
        $response->assertSessionHas('error');

        // Now force-delete the conflicting trashed record, freeing up that year
        Payment::withTrashed()->find($otherPaymentId)->forceDelete();

        $response = $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $this->currentYear - 1,
        ]);

        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame($this->currentYear - 1, $payment->year_paid);
    }

    /** @test */
    public function it_logs_activity_on_update(): void
    {
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);

        $newYear = $this->currentYear - 1;

        $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $newYear,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'payment',
            'description' => 'payment_updated',
            'subject_id' => $payment->id,
            'subject_type' => Payment::class,
            'causer_id' => $this->superAdmin->id,
            'causer_type' => User::class,
        ]);
    }

    /** @test */
    public function it_updates_member_status_when_year_is_changed(): void
    {
        // Give the member a payment for current year (makes them active)
        $payment = Payment::factory()->create([
            'member_id' => $this->member->id,
            'year_paid' => $this->currentYear,
        ]);
        $this->member->updateStatusFromPayments();
        $this->assertSame('active', $this->member->fresh()->status);

        // Change the payment to last year — member should become inactive
        $lastYear = $this->currentYear - 1;

        $this->put(route('admin.payments.update', $payment), [
            'year_paid' => $lastYear,
        ]);

        $this->member->refresh();
        $this->assertSame('inactive', $this->member->status);
    }
}
