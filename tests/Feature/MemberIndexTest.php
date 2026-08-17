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

class MemberIndexTest extends TestCase
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

    private function seedClubAndPosition(): array
    {
        $region = Region::factory()->create(['name' => 'Region A']);
        $club = Club::factory()->create(['name' => 'Club A', 'region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        return [$region, $club, $position];
    }

    // ─────────────────────────────────────────────
    //  SORTING
    // ─────────────────────────────────────────────

    /** @test */
    public function index_sorts_by_name_ascending_by_default(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Zoe',
            'last_name' => 'Adams',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Amy',
            'last_name' => 'Smith',
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['Zoe Adams', 'Amy Smith']);
    }

    /** @test */
    public function index_can_sort_by_name_descending(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Zoe',
            'last_name' => 'Adams',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Amy',
            'last_name' => 'Smith',
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index', ['sort' => 'name_desc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Amy Smith', 'Zoe Adams']);
    }

    /** @test */
    public function index_can_sort_by_club_name(): void
    {
        $region = Region::factory()->create(['name' => 'Region A']);
        $alphaClub = Club::factory()->create(['name' => 'Alpha Club', 'region_id' => $region->id]);
        $zuluClub = Club::factory()->create(['name' => 'Zulu Club', 'region_id' => $region->id]);
        $position = Position::factory()->create(['name' => 'Member']);

        // Last names chosen so the club sort order differs from the name sort order.
        Member::factory()->create([
            'club_id' => $zuluClub->id,
            'position_id' => $position->id,
            'first_name' => 'Adam',
            'last_name' => 'First',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $alphaClub->id,
            'position_id' => $position->id,
            'first_name' => 'Beth',
            'last_name' => 'Second',
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index', ['sort' => 'club']));

        $response->assertOk();
        $response->assertSeeInOrder(['Beth Second', 'Adam First']);
    }

    /** @test */
    public function index_can_sort_by_status(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        // Names chosen so the status sort order differs from the name sort order.
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Alpha',
            'last_name' => 'Zeta',
            'status' => 'active',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Beta',
            'last_name' => 'Alpha',
            'status' => 'inactive',
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index', ['sort' => 'status']));

        $response->assertOk();
        $response->assertSeeInOrder(['Alpha Zeta', 'Beta Alpha']);
    }

    /** @test */
    public function index_can_sort_by_created_at_descending(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Old',
            'last_name' => 'One',
            'created_at' => now()->subDays(2),
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'New',
            'last_name' => 'Two',
            'created_at' => now(),
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index', ['sort' => 'created_at_desc']));

        $response->assertOk();
        $response->assertSeeInOrder(['New Two', 'Old One']);
    }

    /** @test */
    public function index_falls_back_to_name_sort_for_unknown_sort_values(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Zoe',
            'last_name' => 'Adams',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Amy',
            'last_name' => 'Smith',
            'middle_initial' => null,
            'suffix' => null,
        ]);

        $response = $this->get(route('admin.members.index', ['sort' => 'not-a-real-sort']));

        $response->assertOk();
        $response->assertSeeInOrder(['Zoe Adams', 'Amy Smith']);
    }

    // ─────────────────────────────────────────────
    //  INDEX STATE PERSISTENCE
    // ─────────────────────────────────────────────

    /** @test */
    public function store_redirects_back_to_the_last_filtered_index(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        $this->get(route('admin.members.index', ['status' => 'inactive', 'sort' => 'created_at_desc']));

        $response = $this->post(route('admin.members.store'), [
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'New',
            'last_name' => 'Member',
            'contact_number' => '09170000001',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index', ['status' => 'inactive', 'sort' => 'created_at_desc']));
    }

    /** @test */
    public function update_redirects_back_to_the_last_filtered_index(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        $member = Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Existing',
            'last_name' => 'Member',
        ]);

        $this->get(route('admin.members.index', ['status' => 'inactive', 'sort' => 'club']));

        $response = $this->patch(route('admin.members.update', $member), [
            'club_id' => $member->club_id,
            'position_id' => $member->position_id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'contact_number' => $member->contact_number,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index', ['status' => 'inactive', 'sort' => 'club']));
    }

    /** @test */
    public function store_preserves_the_last_index_page_number(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        // 11 members → 2 pages at 10 per page, so page 2 is valid.
        Member::factory()->count(11)->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
        ]);

        $this->get(route('admin.members.index', ['page' => 2]));

        $response = $this->post(route('admin.members.store'), [
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'New',
            'last_name' => 'Member',
            'contact_number' => '09170000002',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index', ['page' => 2]));
    }

    /** @test */
    public function store_redirects_to_a_plain_index_when_no_state_was_remembered(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        $response = $this->post(route('admin.members.store'), [
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'New',
            'last_name' => 'Member',
            'contact_number' => '09170000003',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index'));
    }

    /** @test */
    public function index_can_filter_to_members_without_a_photo(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'No',
            'last_name' => 'Photo',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Has',
            'last_name' => 'Photo',
            'middle_initial' => null,
            'suffix' => null,
            'profile_picture' => 'profile-pictures/img_test.webp',
        ]);

        $response = $this->get(route('admin.members.index', ['photo' => 'missing']));

        $response->assertOk();
        $response->assertSee('No Photo');
        $response->assertDontSee('Has Photo');
    }

    /** @test */
    public function index_can_filter_to_members_with_a_photo(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'No',
            'last_name' => 'Photo',
            'middle_initial' => null,
            'suffix' => null,
        ]);
        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Has',
            'last_name' => 'Photo',
            'middle_initial' => null,
            'suffix' => null,
            'profile_picture' => 'profile-pictures/img_test.webp',
        ]);

        $response = $this->get(route('admin.members.index', ['photo' => 'has']));

        $response->assertOk();
        $response->assertSee('Has Photo');
        $response->assertDontSee('No Photo');
    }

    /** @test */
    public function update_redirects_back_to_the_last_photo_filtered_index(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        $member = Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Existing',
            'last_name' => 'Member',
        ]);

        $this->get(route('admin.members.index', ['photo' => 'missing', 'sort' => 'club']));

        $response = $this->patch(route('admin.members.update', $member), [
            'club_id' => $member->club_id,
            'position_id' => $member->position_id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'contact_number' => $member->contact_number,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.members.index', ['photo' => 'missing', 'sort' => 'club']));
    }

    /** @test */
    public function index_falls_back_to_the_last_page_when_the_requested_page_is_out_of_range(): void
    {
        [$region, $club, $position] = $this->seedClubAndPosition();

        Member::factory()->create([
            'club_id' => $club->id,
            'position_id' => $position->id,
            'first_name' => 'Only',
            'last_name' => 'Member',
        ]);

        $response = $this->get(route('admin.members.index', ['page' => 2]));

        $response->assertRedirect(route('admin.members.index', ['page' => 1]));
    }
}
