<?php

namespace Tests\Unit\Livewire;

use App\Livewire\HealthIndicator;
use App\Models\User;
use App\Services\HealthyDayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class HealthIndicatorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_component_initialization_and_property_setup()
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->with($this->user, Mockery::type(Carbon::class))
                   ->once()
                   ->andReturn(true);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->assertSet('selectedDate', $today)
            ->assertSet('modalVisible', false)
            ->assertSet('ruleStatuses', [])
            ->assertSet('isHealthy', true);
    }

    public function test_mount_with_custom_date()
    {
        $customDate = '2025-08-05';
        
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->with($this->user, Mockery::type(Carbon::class))
                   ->once()
                   ->andReturn(false);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        Livewire::test(HealthIndicator::class, ['selectedDate' => $customDate])
            ->assertSet('selectedDate', $customDate)
            ->assertSet('isHealthy', false);
    }

    public function test_modal_toggle_functionality()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')->andReturn(true);
        $mockService->shouldReceive('getRuleStatuses')
                   ->once()
                   ->andReturn([
                       'rybelsus_morning' => [
                           'time' => '09:00',
                           'description' => 'Rybelsus medication taken',
                           'active' => true,
                           'met' => true
                       ]
                   ]);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Test opening modal
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true)
                 ->assertSet('ruleStatuses', [
                     'rybelsus_morning' => [
                         'time' => '09:00',
                         'description' => 'Rybelsus medication taken',
                         'active' => true,
                         'met' => true
                     ]
                 ]);
        
        // Test closing modal
        $component->call('toggleModal')
                 ->assertSet('modalVisible', false);
    }

    public function test_close_modal_method()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')->andReturn(true);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        Livewire::test(HealthIndicator::class)
            ->set('modalVisible', true)
            ->call('closeModal')
            ->assertSet('modalVisible', false);
    }

    public function test_real_time_updates_when_measurements_change()
    {
        // Mock the HealthyDayService with different return values
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->twice()
                   ->andReturn(false, true); // First false, then true after measurement
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->assertSet('isHealthy', false);
        
        // Simulate measurement saved event
        $component->dispatch('measurement-saved')
                 ->assertSet('isHealthy', true);
    }

    public function test_measurement_updated_event_refreshes_status()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->twice()
                   ->andReturn(true, false);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->assertSet('isHealthy', true);
        
        // Simulate measurement updated event
        $component->dispatch('measurement-updated')
                 ->assertSet('isHealthy', false);
    }

    public function test_measurement_deleted_event_refreshes_status()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->twice()
                   ->andReturn(true, false);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->assertSet('isHealthy', true);
        
        // Simulate measurement deleted event
        $component->dispatch('measurement-deleted')
                 ->assertSet('isHealthy', false);
    }

    public function test_dashboard_date_changed_event_updates_date()
    {
        $newDate = '2025-08-06';
        
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->twice()
                   ->andReturn(true, false);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->set('modalVisible', true)
            ->assertSet('isHealthy', true);
        
        // Simulate dashboard date change
        $component->dispatch('dashboard-date-changed', $newDate)
                 ->assertSet('selectedDate', $newDate)
                 ->assertSet('modalVisible', false) // Modal should close
                 ->assertSet('isHealthy', false);
    }

    public function test_selected_date_update_closes_modal()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')
                   ->twice()
                   ->andReturn(true);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class)
            ->set('modalVisible', true)
            ->set('selectedDate', '2025-08-06')
            ->assertSet('modalVisible', false);
    }

    public function test_correct_smiley_display_based_on_rule_compliance()
    {
        // Test happy smiley for healthy day
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')->andReturn(true);
        $this->app->instance(HealthyDayService::class, $mockService);
        
        Livewire::test(HealthIndicator::class)
            ->assertSet('isHealthy', true)
            ->assertSee('😊');
        
        // Test sad smiley for unhealthy day
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')->andReturn(false);
        $this->app->instance(HealthyDayService::class, $mockService);
        
        Livewire::test(HealthIndicator::class)
            ->assertSet('isHealthy', false)
            ->assertSee('😔');
    }

    public function test_modal_contains_escape_key_handler()
    {
        // Mock the HealthyDayService
        $mockService = Mockery::mock(HealthyDayService::class);
        $mockService->shouldReceive('isHealthyDay')->andReturn(true);
        $mockService->shouldReceive('getRuleStatuses')->andReturn([]);
        
        $this->app->instance(HealthyDayService::class, $mockService);
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Open modal
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true);
        
        // Check that the modal HTML contains escape key handling
        $html = $component->html();
        $this->assertStringContainsString('x-on:keydown.escape.window', $html);
        $this->assertStringContainsString('$wire.closeModal()', $html);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}