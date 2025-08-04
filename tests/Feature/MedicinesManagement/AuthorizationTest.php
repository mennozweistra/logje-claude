<?php

namespace Tests\Feature\MedicinesManagement;

use App\Models\User;
use App\Models\Medication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_user_cannot_access_medicines_management()
    {
        $this->get(route('medicines-management'))
            ->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticated_user_can_access_medicines_management()
    {
        $this->actingAs($this->user1);

        $this->get(route('medicines-management'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_see_other_users_medicines_in_listings()
    {
        $userMedicine = Medication::factory()->create([
            'name' => 'My Medicine',
            'user_id' => $this->user1->id,
        ]);
        
        $otherMedicine = Medication::factory()->create([
            'name' => 'Other User Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class);
        
        $component->assertSee($userMedicine->name);
        $component->assertDontSee($otherMedicine->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_edit_other_users_medicine_via_component()
    {
        $otherMedicine = Medication::factory()->create([
            'name' => 'Other User Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        // Attempt to edit other user's medicine should fail
        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('openEditMedicine', $otherMedicine->id);

        $component->assertSee('Medicine not found or you do not have permission to edit it.');
        
        // The modal should not open
        $this->assertFalse($component->get('showModal'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_delete_other_users_medicine_via_component()
    {
        $otherMedicine = Medication::factory()->create([
            'name' => 'Other User Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        // Attempt to delete other user's medicine should fail
        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $otherMedicine->id);

        $component->assertSee('Medicine not found or you do not have permission to delete it.');
        
        // The delete confirmation should not show
        $this->assertFalse($component->get('showDeleteConfirm'));
        
        // Medicine should still exist
        $this->assertDatabaseHas('medications', [
            'id' => $otherMedicine->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_search_only_searches_their_own_medicines()
    {
        $userMedicine = Medication::factory()->create([
            'name' => 'My Searchable Medicine',
            'user_id' => $this->user1->id,
        ]);
        
        $otherMedicine = Medication::factory()->create([
            'name' => 'Searchable Other Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->set('search', 'Searchable');

        // Should only see own medicine in search results
        $component->assertSee($userMedicine->name);
        $component->assertDontSee($otherMedicine->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function medicine_creation_automatically_associates_with_authenticated_user()
    {
        $this->actingAs($this->user1);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->set('name', 'Auto Associated Medicine')
            ->set('description', 'Test Description')
            ->call('save');

        // Verify medicine is associated with the authenticated user
        $this->assertDatabaseHas('medications', [
            'name' => 'Auto Associated Medicine',
            'user_id' => $this->user1->id,
        ]);
    }
}