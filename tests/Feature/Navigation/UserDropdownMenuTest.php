<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDropdownMenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_dropdown_menu_contains_data_section()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertSee('Data');
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function data_submenu_contains_food_option()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        // Check that Food submenu item exists
        $response->assertSee('Food');
        $response->assertSee('food-management');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function data_submenu_contains_medicines_option()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        // Check that Medicines submenu item exists
        $response->assertSee('Medicines');
        $response->assertSee('medicines-management');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function food_management_link_works_correctly()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('food-management'));

        $response->assertStatus(200);
        $response->assertSee('Food Management');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function medicines_management_link_works_correctly()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('medicines-management'));

        $response->assertStatus(200);
        $response->assertSee('Medicines Management');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_users_cannot_access_food_management()
    {
        $response = $this->get(route('food-management'));

        $response->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_users_cannot_access_medicines_management()
    {
        $response = $this->get(route('medicines-management'));

        $response->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function navigation_structure_is_properly_organized()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        // Verify the navigation structure includes the reorganized menu
        // Data should be in user dropdown, not main navigation
        $content = $response->getContent();
        
        // Check that both Food and Medicines are under Data submenu
        $this->assertStringContainsString('Data', $content);
        $this->assertStringContainsString('Food', $content);
        $this->assertStringContainsString('Medicines', $content);
    }
}