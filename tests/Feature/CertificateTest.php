<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

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
        ]);

        $this->actingAs($this->superAdmin);
    }

    // ─────────────────────────────────────────────
    //  CERTIFICATE CRUD via MEMBER CREATE/UPDATE
    // ─────────────────────────────────────────────

    /** @test */
    public function it_creates_certificates_when_creating_a_member(): void
    {
        $response = $this->post(route('admin.members.store'), [
            'club_id' => $this->member->club_id,
            'position_id' => $this->member->position_id,
            'first_name' => 'New',
            'last_name' => 'Member',
            'contact_number' => '09170000099',
            'certificates' => [
                ['name' => 'Leadership Award', 'issued_at' => '2026-06-01'],
                ['name' => 'Service Recognition', 'issued_at' => '2026-05-15'],
            ],
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index'));

        // Find the newly created member by contact number
        $newMember = Member::where('contact_number', '09170000099')->first();
        $this->assertNotNull($newMember);

        $this->assertDatabaseHas('certificates', [
            'member_id' => $newMember->id,
            'name' => 'Leadership Award',
        ]);
        $this->assertDatabaseHas('certificates', [
            'member_id' => $newMember->id,
            'name' => 'Service Recognition',
        ]);

        $this->assertCount(2, $newMember->certificates);
    }

    /** @test */
    public function it_adds_certificates_to_existing_member(): void
    {
        $response = $this->patch(route('admin.members.update', $this->member), [
            'club_id' => $this->member->club_id,
            'position_id' => $this->member->position_id,
            'first_name' => $this->member->first_name,
            'last_name' => $this->member->last_name,
            'contact_number' => $this->member->contact_number,
            'certificates_managed' => '1',
            'certificates' => [
                ['name' => 'New Award', 'issued_at' => '2026-07-01'],
            ],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('certificates', [
            'member_id' => $this->member->id,
            'name' => 'New Award',
        ]);
    }

    /** @test */
    public function it_updates_existing_certificate_name(): void
    {
        $cert = Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'Original Name',
            'issued_at' => '2025-01-01',
        ]);

        $this->patch(route('admin.members.update', $this->member), [
            'club_id' => $this->member->club_id,
            'position_id' => $this->member->position_id,
            'first_name' => $this->member->first_name,
            'last_name' => $this->member->last_name,
            'contact_number' => $this->member->contact_number,
            'certificates_managed' => '1',
            'certificates' => [
                [
                    'id' => $cert->id,
                    'name' => 'Updated Name',
                    'issued_at' => '2026-06-15',
                ],
            ],
        ]);

        $cert->refresh();
        $this->assertSame('Updated Name', $cert->name);
        // issued_at is not cast to Carbon, so compare as string
        $this->assertStringContainsString('2026-06-15', $cert->issued_at);
    }

    /** @test */
    public function it_removes_certificate_not_included_in_update(): void
    {
        // Create a certificate that should be removed
        $cert = Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'To Be Removed',
        ]);

        // Update without including the certificate — should soft-delete it
        $this->patch(route('admin.members.update', $this->member), [
            'club_id' => $this->member->club_id,
            'position_id' => $this->member->position_id,
            'first_name' => $this->member->first_name,
            'last_name' => $this->member->last_name,
            'contact_number' => $this->member->contact_number,
            'certificates_managed' => '1',
        ]);

        // The certificate should be soft-deleted
        $cert->refresh();
        $this->assertNotNull($cert->deleted_at);
    }

    // ─────────────────────────────────────────────
    //  CASCADE: SOFT-DELETE & RESTORE
    // ─────────────────────────────────────────────

    /** @test */
    public function it_soft_deletes_certificates_when_member_is_deleted(): void
    {
        $cert = Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'Cascade Test Cert',
        ]);

        $this->delete(route('admin.members.destroy', $this->member), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        $cert->refresh();
        $this->assertNotNull($cert->deleted_at);
    }

    /** @test */
    public function it_restores_certificates_when_member_is_restored(): void
    {
        $cert = Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'Restore Test Cert',
        ]);

        // Soft-delete the member (which cascades to certificates)
        $this->delete(route('admin.members.destroy', $this->member), [
            'confirm_delete' => '1',
            'confirm_text' => 'DELETE',
        ]);

        // Restore the member
        $this->patch(route('admin.members.restore', $this->member->id));

        $cert->refresh();
        $this->assertNull($cert->deleted_at);
    }

    // ─────────────────────────────────────────────
    //  DISPLAY ON PUBLIC PROFILE
    // ─────────────────────────────────────────────

    /** @test */
    public function it_shows_certificates_on_active_member_profile(): void
    {
        $this->member->update(['status' => 'active']);

        Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'Profile Cert One',
            'issued_at' => '2026-01-15',
        ]);

        $response = $this->get(route('member.profile', $this->member->slug));

        $response->assertStatus(200);
        $response->assertSee('Certificates & Awards');
        $response->assertSee('Profile Cert One');
    }

    /** @test */
    public function it_shows_certificates_on_renewal_page_for_inactive_member(): void
    {
        $this->member->update(['status' => 'inactive']);

        Certificate::factory()->create([
            'member_id' => $this->member->id,
            'name' => 'Renewal Cert Display',
            'issued_at' => '2025-06-01',
        ]);

        $response = $this->get(route('member.profile', $this->member->slug));

        $response->assertStatus(200);
        $response->assertSee('Renewal');
        $response->assertSee('Renewal Cert Display');
    }
}
