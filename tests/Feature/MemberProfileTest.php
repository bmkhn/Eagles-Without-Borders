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

class MemberProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Member $activeMember;

    protected Member $inactiveMember;

    protected string $activeSlug;

    protected string $inactiveSlug;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $region = Region::factory()->create(['name' => 'Test Region']);
        $club = Club::factory()->create([
            'name' => 'Test Club',
            'region_id' => $region->id,
        ]);
        $position = Position::factory()->create(['name' => 'Member']);

        $this->activeMember = Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Active',
            'last_name' => 'Member',
            'status' => 'active',
            'contact_number' => '09170000001',
        ]);
        $this->activeSlug = $this->activeMember->slug;

        $this->inactiveMember = Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Inactive',
            'last_name' => 'Member',
            'status' => 'inactive',
            'contact_number' => '09170000002',
        ]);
        $this->inactiveSlug = $this->inactiveMember->slug;
    }

    /** @test */
    public function active_member_profile_is_publicly_accessible(): void
    {
        $response = $this->get(route('member.profile', $this->activeSlug));

        $response->assertStatus(200);
        $response->assertSee($this->activeMember->first_name);
        $response->assertSee($this->activeMember->last_name);
        $response->assertSee('Active');
    }

    /** @test */
    public function active_member_profile_shows_membership_years(): void
    {
        // Give the active member a payment record
        Payment::factory()->create([
            'member_id' => $this->activeMember->id,
            'year_paid' => (int) now()->year,
        ]);

        $response = $this->get(route('member.profile', $this->activeSlug));

        $response->assertStatus(200);
        $response->assertSee((string) now()->year);
    }

    /** @test */
    public function inactive_member_profile_shows_renewal_page_for_guests(): void
    {
        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee('Renewal');
        $response->assertSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_is_accessible_to_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee($this->inactiveMember->first_name);
        $response->assertDontSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_is_accessible_to_national_admin(): void
    {
        $nationalAdmin = User::factory()->create();
        $nationalAdmin->assignRole('national-admin');

        $this->actingAs($nationalAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee($this->inactiveMember->first_name);
        $response->assertDontSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_is_accessible_to_regional_admin_of_same_region(): void
    {
        $region = $this->inactiveMember->club->region;

        $regionalAdmin = User::factory()->create([
            'region_id' => $region->id,
        ]);
        $regionalAdmin->assignRole('regional-admin');

        $this->actingAs($regionalAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee($this->inactiveMember->first_name);
        $response->assertDontSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_shows_renewal_for_regional_admin_of_different_region(): void
    {
        $otherRegion = Region::factory()->create(['name' => 'Other Region']);

        $regionalAdmin = User::factory()->create([
            'region_id' => $otherRegion->id,
        ]);
        $regionalAdmin->assignRole('regional-admin');

        $this->actingAs($regionalAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_is_accessible_to_club_admin_of_same_club(): void
    {
        $club = $this->inactiveMember->club;

        $clubAdmin = User::factory()->create([
            'club_id' => $club->id,
        ]);
        $clubAdmin->assignRole('club-admin');

        $this->actingAs($clubAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee($this->inactiveMember->first_name);
        $response->assertDontSee('membership is inactive');
    }

    /** @test */
    public function inactive_member_profile_shows_renewal_for_club_admin_of_different_club(): void
    {
        $otherClub = Club::factory()->create(['name' => 'Other Club']);

        $clubAdmin = User::factory()->create([
            'club_id' => $otherClub->id,
        ]);
        $clubAdmin->assignRole('club-admin');

        $this->actingAs($clubAdmin);

        $response = $this->get(route('member.profile', $this->inactiveSlug));

        $response->assertStatus(200);
        $response->assertSee('membership is inactive');
    }

    /** @test */
    public function it_shows_member_not_found_page_for_non_existent_slug(): void
    {
        $response = $this->get(route('member.profile', 'non-existent-slug'));

        $response->assertStatus(200);
        $response->assertSee('Member Not Found');
        $response->assertSee('check the URL');
        $response->assertSee('typos or spelling');
    }

    /** @test */
    public function active_member_profile_shows_contact_number(): void
    {
        $response = $this->get(route('member.profile', $this->activeSlug));

        $response->assertStatus(200);
        $response->assertSee($this->activeMember->contact_number);
    }
}
