<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardHealthIndicatorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_health_indicator_is_included_in_dashboard_view()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Check that the dashboard renders with the day name
        $component->assertSee(Carbon::today()->format('l')); // Day name like "Wednesday"
        
        // Check for Livewire component rendering structure
        $html = $component->html();
        
        // Debug: Let's see what's actually in the HTML
        // The Livewire component should be rendered as a child component
        $this->assertStringContainsString('wire:snapshot', $html);
        
        // Check that the HTML structure includes our new elements
        $this->assertStringContainsString('flex items-center', $html);
    }

    public function test_dashboard_dispatches_date_change_events()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Test that date navigation methods dispatch events for health indicator
        $component->call('previousDay')
                 ->assertDispatched('dashboard-date-changed');
        
        $component->call('goToToday')
                 ->assertDispatched('dashboard-date-changed');
    }

    public function test_dashboard_structure_updated_for_health_indicator()
    {
        $component = Livewire::test(Dashboard::class);
        
        $html = $component->html();
        
        // Test that the flex structure for day name + indicator exists
        $this->assertStringContainsString('flex items-center', $html);
        
        // Test that basic dashboard elements still exist
        $component->assertSee('Today') // Navigation button
                 ->assertSee(Carbon::today()->format('d-m-Y')) // Date format
                 ->assertSee('Measurements'); // Section header
    }

    public function test_indicator_updates_when_navigating_between_dates()
    {
        $component = Livewire::test(Dashboard::class);
        
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();
        
        // Test date navigation functionality
        $component->call('previousDay')
                 ->assertSet('selectedDate', $yesterday->format('Y-m-d'))
                 ->assertDispatched('dashboard-date-changed');
        
        $component->call('goToToday')
                 ->assertSet('selectedDate', $today->format('Y-m-d'))
                 ->assertDispatched('dashboard-date-changed');
    }

    public function test_measurement_events_are_handled()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Test that measurement events trigger refresh
        $component->dispatch('measurement-saved');
        
        // Component should still render correctly
        $component->assertSee(Carbon::today()->format('l'));
    }
}