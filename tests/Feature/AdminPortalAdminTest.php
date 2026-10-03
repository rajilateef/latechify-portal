<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Smoke-tests the admin screens added for checkout, fee tracking and class monitoring. */
class AdminPortalAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function admin(): User
    {
        return User::where('is_admin', true)->firstOrFail();
    }

    public static function adminPages(): array
    {
        return [
            'checkout orders' => ['/admin/checkout-orders'],
            'fee payments'    => ['/admin/fee-payments'],
            'class monitor'   => ['/admin/class-monitor'],
            'dashboard'       => ['/admin'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_page_loads(string $url): void
    {
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    #[DataProvider('adminPages')]
    public function test_a_trainee_cannot_reach_the_admin_panel(string $url): void
    {
        $student = User::where('email', 'student@latechify.test')->firstOrFail();

        $response = $this->actingAs($student)->get($url);

        $this->assertContains($response->getStatusCode(), [302, 403], "Expected {$url} to be closed to trainees.");
    }

    public function test_the_class_monitor_reports_cohort_progress(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/class-monitor')
            ->assertOk()
            ->assertSee('Syllabus coverage')
            ->assertSee('Average progress')
            ->assertSee('Demo Student');
    }
}
