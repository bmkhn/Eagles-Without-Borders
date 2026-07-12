<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    // ─────────────────────────────────────────────
    //  ACCESS CONTROL
    // ─────────────────────────────────────────────

    /** @test */
    public function guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function super_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    /** @test */
    public function national_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('national-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    /** @test */
    public function regional_admin_can_access_dashboard(): void
    {
        $region = Region::factory()->create(['name' => 'Test Region']);
        $user = User::factory()->create(['region_id' => $region->id]);
        $user->assignRole('regional-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    /** @test */
    public function club_admin_can_access_dashboard(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);
        $user = User::factory()->create(['club_id' => $club->id]);
        $user->assignRole('club-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    // ─────────────────────────────────────────────
    //  SUPER / NATIONAL ADMIN — STATS & SECTIONS
    // ─────────────────────────────────────────────

    /** @test */
    public function national_level_dashboard_shows_stats(): void
    {
        $region = Region::factory()->create(['name' => 'Palawan']);
        $club = Club::factory()->create([
            'name' => 'Test Club',
            'region_id' => $region->id,
        ]);
        $position = Position::factory()->create(['name' => 'Member']);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Regions');
        $response->assertSee('Clubs');
        $response->assertSee('Positions');
        $response->assertSee('Members');
    }

    /** @test */
    public function national_level_dashboard_shows_club_membership_breakdown(): void
    {
        $region = Region::factory()->create(['name' => 'Palawan']);
        $club = Club::factory()->create([
            'name' => 'Eagles Club',
            'region_id' => $region->id,
        ]);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'status' => 'active',
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'status' => 'inactive',
        ]);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Club Membership Status');
        $response->assertSee('Eagles Club');
        $response->assertSee('1 Active');
        $response->assertSee('1 Inactive');
    }

    /** @test */
    public function national_level_dashboard_shows_quick_actions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Quick Actions');
        $response->assertSee('Create Region');
        $response->assertSee('Create Club');
        $response->assertSee('Create Position');
        $response->assertSee('Create Admin');
    }

    /** @test */
    public function national_level_dashboard_shows_manage_links(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manage');
        $response->assertSee('Manage Regions');
        $response->assertSee('Manage Clubs');
        $response->assertSee('Manage Positions');
    }

    // ─────────────────────────────────────────────
    //  REGIONAL ADMIN — SCOPED VIEW
    // ─────────────────────────────────────────────

    /** @test */
    public function regional_admin_sees_their_region_name(): void
    {
        $region = Region::factory()->create(['name' => 'Palawan Region 8']);
        $club = Club::factory()->create([
            'name' => 'Palawan Club',
            'region_id' => $region->id,
        ]);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
        ]);

        $user = User::factory()->create(['region_id' => $region->id]);
        $user->assignRole('regional-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Your Region');
        $response->assertSee('Palawan Region 8');
    }

    /** @test */
    public function regional_admin_sees_their_regions_club_count(): void
    {
        $region = Region::factory()->create(['name' => 'Region A']);
        Club::factory()->create(['region_id' => $region->id, 'name' => 'Club A']);
        Club::factory()->create(['region_id' => $region->id, 'name' => 'Club B']);

        $user = User::factory()->create(['region_id' => $region->id]);
        $user->assignRole('regional-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Clubs', '2']);
    }

    /** @test */
    public function regional_admin_sees_regional_membership_status(): void
    {
        $region = Region::factory()->create(['name' => 'Region A']);
        $club = Club::factory()->create(['region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create(['region_id' => $region->id]);
        $user->assignRole('regional-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Regional Membership Status');
    }

    // ─────────────────────────────────────────────
    //  CLUB ADMIN — SCOPED VIEW
    // ─────────────────────────────────────────────

    /** @test */
    public function club_admin_sees_their_club_name(): void
    {
        $region = Region::factory()->create(['name' => 'Test Region']);
        $club = Club::factory()->create([
            'name' => 'My Eagles Club',
            'region_id' => $region->id,
        ]);

        $user = User::factory()->create(['club_id' => $club->id]);
        $user->assignRole('club-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Your Club');
        $response->assertSee('My Eagles Club');
    }

    /** @test */
    public function club_admin_sees_their_clubs_member_count(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create(['club_id' => $club->id, 'position_id' => $position->id]);
        Member::factory()->create(['club_id' => $club->id, 'position_id' => $position->id]);
        Member::factory()->create(['club_id' => $club->id, 'position_id' => $position->id]);

        $user = User::factory()->create(['club_id' => $club->id]);
        $user->assignRole('club-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Members', '3']);
    }

    /** @test */
    public function club_admin_sees_club_membership_status(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'status' => 'active',
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'status' => 'inactive',
        ]);

        $user = User::factory()->create(['club_id' => $club->id]);
        $user->assignRole('club-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Club Membership Status');
    }

    // ─────────────────────────────────────────────
    //  RECENT MEMBERS
    // ─────────────────────────────────────────────

    /** @test */
    public function dashboard_shows_recent_members(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        // Explicitly set middle_initial to null to avoid random initial in name
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Recent',
            'last_name' => 'Member',
            'middle_initial' => null,
        ]);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Members');
        $response->assertSee('Recent Member');
    }

    /** @test */
    public function recent_members_are_scoped_for_club_admin(): void
    {
        $region = Region::factory()->create();
        $clubA = Club::factory()->create(['region_id' => $region->id, 'name' => 'Club A']);
        $clubB = Club::factory()->create(['region_id' => $region->id, 'name' => 'Club B']);
        $position = Position::factory()->create(['name' => 'Member']);

        Member::factory()->create([
            'club_id' => $clubA->id,
            'position_id' => $position->id,
            'first_name' => 'InClub',
            'last_name' => 'Member',
            'middle_initial' => null,
        ]);
        Member::factory()->create([
            'club_id' => $clubB->id,
            'position_id' => $position->id,
            'first_name' => 'OtherClub',
            'last_name' => 'Member',
            'middle_initial' => null,
        ]);

        $user = User::factory()->create(['club_id' => $clubA->id]);
        $user->assignRole('club-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('InClub Member');
        $response->assertDontSee('OtherClub Member');
    }

    // ─────────────────────────────────────────────
    //  ROLE-SPECIFIC UI DIFFERENCES
    // ─────────────────────────────────────────────

    /** @test */
    public function super_admin_sees_create_admin_link(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Create Admin');
    }

    /** @test */
    public function national_admin_does_not_see_create_admin_link(): void
    {
        $user = User::factory()->create();
        $user->assignRole('national-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Create Admin');
    }

    /** @test */
    public function national_level_dashboard_shows_position_counts(): void
    {
        $region = Region::factory()->create();
        $club = Club::factory()->create(['region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'President']);

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Position Counts');
        $response->assertSee('1');
        $response->assertSee('President');
    }

    /** @test */
    public function regional_admin_recent_members_are_scoped(): void
    {
        $regionA = Region::factory()->create(['name' => 'Region A']);
        $regionB = Region::factory()->create(['name' => 'Region B']);
        $clubA = Club::factory()->create(['region_id' => $regionA->id, 'name' => 'Club A']);
        $clubB = Club::factory()->create(['region_id' => $regionB->id, 'name' => 'Club B']);
        $position = Position::factory()->create(['name' => 'Member']);

        // Explicitly set middle_initial to null to avoid random initial in name
        Member::factory()->create([
            'club_id' => $clubA->id,
            'position_id' => $position->id,
            'first_name' => 'InRegion',
            'last_name' => 'Member',
            'middle_initial' => null,
        ]);
        Member::factory()->create([
            'club_id' => $clubB->id,
            'position_id' => $position->id,
            'first_name' => 'OtherRegion',
            'last_name' => 'Member',
            'middle_initial' => null,
        ]);

        $user = User::factory()->create(['region_id' => $regionA->id]);
        $user->assignRole('regional-admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('InRegion Member');
        $response->assertDontSee('OtherRegion Member');
    }
}
