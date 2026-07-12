<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function public_404_shows_custom_error_page(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Page Not Found');
        $response->assertSee('does not exist or has been moved');
        $response->assertSee('Go Home');
    }

    /** @test */
    public function public_404_shows_eagles_branding(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Eagles');
        $response->assertSee('Without Borders');
        $response->assertSee('Admin Login');
    }

    /** @test */
    public function admin_404_shows_admin_styled_error_page(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Page Not Found');
        $response->assertSee('does not exist or has been moved');
        $response->assertSee('Go to Dashboard');
    }

    /** @test */
    public function admin_404_does_not_show_public_go_home_button(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Go to Dashboard');
        $response->assertDontSee('Go Home');
    }



    /** @test */
    public function member_not_found_page_shows_dashboard_link_for_authenticated_users(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->get(route('member.profile', 'non-existent-slug'));

        $response->assertStatus(200);
        $response->assertSee('Member Not Found');
        $response->assertSee('Dashboard');
        $response->assertSee('Go Home');
    }

    /** @test */
    public function member_not_found_page_shows_for_authenticated_non_admin_user(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser);

        $response = $this->get(route('member.profile', 'non-existent-slug'));

        $response->assertStatus(200);
        $response->assertSee('Member Not Found');
        $response->assertSee('check the URL');
        $response->assertSee('Dashboard');
    }

    // ----------------------------------------------------------------
    // 403 Forbidden
    // ----------------------------------------------------------------

    /** @test */
    public function public_403_shows_access_denied_page(): void
    {
        $response = $this->view('errors.403');

        $response->assertSee('403');
        $response->assertSee('Access Denied');
        $response->assertSee("don't have permission");
        $response->assertSee('Go Home');
    }

    /** @test */
    public function public_403_shows_eagles_branding(): void
    {
        $response = $this->view('errors.403');

        $response->assertSee('Eagles');
        $response->assertSee('Without Borders');
        $response->assertDontSee('Go to Dashboard');
    }

    /** @test */
    public function admin_403_shows_admin_styled_error_page(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->view('errors.403');

        $response->assertSee('403');
        $response->assertSee('Access Denied');
        $response->assertSee("don't have permission");
        $response->assertSee('Go to Dashboard');
        $response->assertDontSee('Go Home');
    }

    // ----------------------------------------------------------------
    // 419 Session Expired
    // ----------------------------------------------------------------

    /** @test */
    public function public_419_shows_session_expired_page(): void
    {
        $response = $this->view('errors.419');

        $response->assertSee('419');
        $response->assertSee('Session Expired');
        $response->assertSee('session has expired');
        $response->assertSee('Go Home');
        $response->assertSee('Go Back');
    }

    /** @test */
    public function public_419_shows_eagles_branding(): void
    {
        $response = $this->view('errors.419');

        $response->assertSee('Eagles');
        $response->assertSee('Without Borders');
        $response->assertDontSee('Dashboard');
    }

    /** @test */
    public function admin_419_shows_admin_styled_error_page(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->view('errors.419');

        $response->assertSee('419');
        $response->assertSee('Session Expired');
        $response->assertSee('session has expired');
        $response->assertSee('Dashboard');
        $response->assertSee('Go Back');
        $response->assertDontSee('Go Home');
    }

    // ----------------------------------------------------------------
    // 500 Server Error
    // ----------------------------------------------------------------

    /** @test */
    public function public_500_shows_server_error_page(): void
    {
        $response = $this->view('errors.500');

        $response->assertSee('500');
        $response->assertSee('Server Error');
        $response->assertSee('Something went wrong');
        $response->assertSee('Go Home');
    }

    /** @test */
    public function public_500_shows_eagles_branding(): void
    {
        $response = $this->view('errors.500');

        $response->assertSee('Eagles');
        $response->assertSee('Without Borders');
        $response->assertDontSee('Go to Dashboard');
    }

    /** @test */
    public function admin_500_shows_admin_styled_error_page(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin);

        $response = $this->view('errors.500');

        $response->assertSee('500');
        $response->assertSee('Server Error');
        $response->assertSee('Something went wrong');
        $response->assertSee('Go to Dashboard');
        $response->assertDontSee('Go Home');
    }
}
