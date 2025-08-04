<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DateEntryRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_does_not_contain_date_input_field()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain date input elements
        $this->assertStringNotContainsString('type="date"', $content);
        $this->assertStringNotContainsString('input[type=date]', $content);
        $this->assertStringNotContainsString('date-input', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_still_has_navigation_buttons()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Navigation buttons should still be present (using wire:click methods and Today text)
        $this->assertStringContainsString('wire:click="previousDay"', $content);
        $this->assertStringContainsString('Today', $content);
        $this->assertStringContainsString('wire:click="nextDay"', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function navigation_buttons_are_positioned_correctly()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        // Navigation buttons should be positioned to the right of header
        $content = $response->getContent();
        
        // Look for navigation button container with right-aligned elements
        $this->assertStringContainsString('wire:click="previousDay"', $content);
        $this->assertStringContainsString('Today', $content);
        $this->assertStringContainsString('wire:click="nextDay"', $content);
        $this->assertStringContainsString('justify-between', $content); // Layout positioning
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function date_navigation_functionality_still_works()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Get initial date
        $initialDate = $component->get('selectedDate');

        // Test previous day functionality
        $component->call('previousDay');
        $newDate = $component->get('selectedDate');
        
        $this->assertNotEquals($initialDate, $newDate);

        // Test today functionality
        $component->call('goToToday');
        $todayDate = $component->get('selectedDate');
        
        // Should be today's date
        $this->assertEquals(today()->format('Y-m-d'), $todayDate);

        // Test next day functionality - go to previous day first, then next day should work
        $component->call('previousDay');
        $component->call('nextDay');
        $nextDate = $component->get('selectedDate');
        
        $this->assertEquals($todayDate, $nextDate); // Should return to today
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_component_does_not_have_date_input_method()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // Check that the removed date input method is not present in the component's public methods
        $reflection = new \ReflectionClass(\App\Livewire\Dashboard::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(function($method) {
            return $method->getName();
        }, $methods);

        // The updatedSelectedDateDisplay method should not exist
        $this->assertNotContains('updatedSelectedDateDisplay', $methodNames);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mobile_layout_improved_with_date_entry_removal()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Header should have more space without date input
        // Look for simplified header structure
        $this->assertStringNotContainsString('date-input', $content);
        $this->assertStringNotContainsString('selectedDateDisplay', $content);
        
        // Navigation should still be present - using wire:click methods instead of text
        $this->assertStringContainsString('wire:click="previousDay"', $content);
        $this->assertStringContainsString('Today', $content);
        $this->assertStringContainsString('wire:click="nextDay"', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_header_is_cleaner_without_date_input()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        // The header should be simplified and cleaner
        $content = $response->getContent();
        
        // Should not contain complex date input UI elements
        $this->assertStringNotContainsString('wire:model="selectedDateDisplay"', $content);
        $this->assertStringNotContainsString('wire:change="', $content);
        
        // But should still show the current date in some form
        $today = today()->format('d-m-Y'); // Dutch format
        $this->assertStringContainsString($today, $content);
    }

    #[\PHPUnit\Framework\Attributes\Test] 
    public function date_display_format_remains_dutch()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\Dashboard::class);

        // The date should still be displayed in Dutch format (dd-mm-yyyy)
        $selectedDate = $component->get('selectedDate');
        
        // Convert to Dutch format for display
        $dutchDate = \Carbon\Carbon::parse($selectedDate)->format('d-m-Y');
        
        // Should contain Dutch formatted date
        $component->assertSee($dutchDate);
    }
}