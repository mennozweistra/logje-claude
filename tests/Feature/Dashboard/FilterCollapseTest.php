<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilterCollapseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filters_are_collapsed_by_default()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Filters should be hidden by default (using showFilters property)
        $this->assertFalse($component->get('showFilters'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_toggle_filters_visibility()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Initially collapsed
        $this->assertFalse($component->get('showFilters'));

        // Toggle to show filters
        $component->call('toggleFilters');
        $this->assertTrue($component->get('showFilters'));

        // Toggle to hide filters
        $component->call('toggleFilters');
        $this->assertFalse($component->get('showFilters'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filters_auto_show_when_filter_is_applied()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Initially collapsed
        $this->assertFalse($component->get('showFilters'));

        // Apply a filter - this should auto-show the filters
        $component->set('filterTypes', ['weight']);

        // Filters should now be visible
        $this->assertTrue($component->get('showFilters'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_hide_filters_even_when_active()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Apply a filter to auto-show filters
        $component->set('filterTypes', ['weight']);
        $this->assertTrue($component->get('showFilters'));

        // Should be able to manually hide even with active filters
        $component->call('toggleFilters');
        $this->assertFalse($component->get('showFilters'));

        // Filter should still be active
        $this->assertContains('weight', $component->get('filterTypes'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_renders_filter_toggle_button()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        // Should contain filter toggle functionality
        $content = $response->getContent();
        
        // Look for filter-related elements (button or toggle)
        $this->assertStringContainsString('Filter', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function measurement_type_filters_are_present()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Show filters to verify they exist
        $component->call('toggleFilters');

        // Check that all measurement type filters are available
        $component->assertSee('Weight');
        $component->assertSee('Glucose');
        $component->assertSee('Medication');
        $component->assertSee('Food');
        $component->assertSee('Exercise');
        $component->assertSee('Notes');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filter_state_persists_during_page_interactions()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Show filters and apply one
        $component->call('toggleFilters');
        $component->set('filterTypes', ['glucose']);

        // Filters should still be visible after applying filter
        $this->assertTrue($component->get('showFilters'));
        $this->assertContains('glucose', $component->get('filterTypes'));

        // Perform another action (like changing date)
        $component->call('previousDay');

        // Filter state should persist
        $this->assertContains('glucose', $component->get('filterTypes'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function collapsed_filters_reduce_visual_clutter()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        // When filters are collapsed, filter checkboxes should not be immediately visible
        // This test ensures the default collapsed state reduces visual complexity
        $content = $response->getContent();
        
        // The dashboard should load without immediately showing all filter options
        // (Implementation may vary, but concept is filters hidden by default)
        $this->assertStringNotContainsString('checked', $content);
    }
}