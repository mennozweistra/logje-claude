<?php

namespace Tests\Feature\MedicinesManagement;

use App\Models\User;
use App\Models\Medication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserSpecificCrudTest extends TestCase
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
    public function user_can_create_medicine()
    {
        $this->actingAs($this->user1);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->set('name', 'Test Medicine')
            ->set('description', 'Test Description')
            ->call('save');

        $this->assertDatabaseHas('medications', [
            'name' => 'Test Medicine',
            'description' => 'Test Description',
            'user_id' => $this->user1->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_only_see_their_own_medicines()
    {
        // Create medicines for both users
        $medicine1 = Medication::factory()->create([
            'name' => 'User1 Medicine',
            'user_id' => $this->user1->id,
        ]);
        
        $medicine2 = Medication::factory()->create([
            'name' => 'User2 Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class);
        
        // User1 should only see their medicine
        $component->assertSee($medicine1->name);
        $component->assertDontSee($medicine2->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_edit_their_own_medicine()
    {
        $medicine = Medication::factory()->create([
            'name' => 'Original Name',
            'user_id' => $this->user1->id,
        ]);

        $this->actingAs($this->user1);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('openEditMedicine', $medicine->id)
            ->set('name', 'Updated Name')
            ->set('description', 'Updated Description')
            ->call('save');

        $this->assertDatabaseHas('medications', [
            'id' => $medicine->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'user_id' => $this->user1->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_edit_another_users_medicine()
    {
        $medicine = Medication::factory()->create([
            'name' => 'Other User Medicine',
            'user_id' => $this->user2->id,
        ]);

        $this->actingAs($this->user1);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('openEditMedicine', $medicine->id);

        // The medicine won't be found due to global scope, so no errors but flash message should show
        $component->assertSee('Medicine not found or you do not have permission to edit it.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_delete_their_own_medicine()
    {
        $medicine = Medication::factory()->create([
            'name' => 'Medicine to Delete',
            'user_id' => $this->user1->id,
        ]);

        $this->actingAs($this->user1);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $medicine->id)
            ->call('delete');

        $this->assertDatabaseMissing('medications', [
            'id' => $medicine->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function duplicate_medicine_names_allowed_across_different_users()
    {
        // User1 creates a medicine
        Medication::factory()->create([
            'name' => 'Common Medicine',
            'user_id' => $this->user1->id,
        ]);

        // User2 should be able to create a medicine with the same name
        $this->actingAs($this->user2);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->set('name', 'Common Medicine')
            ->set('description', 'User2 Description')
            ->call('save');

        // Check without global scope to count all medicines
        $this->assertEquals(2, Medication::withoutGlobalScopes()->where('name', 'Common Medicine')->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function duplicate_medicine_names_not_allowed_for_same_user()
    {
        // User1 creates a medicine
        Medication::factory()->create([
            'name' => 'Duplicate Medicine',
            'user_id' => $this->user1->id,
        ]);

        $this->actingAs($this->user1);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->set('name', 'Duplicate Medicine')
            ->set('description', 'Duplicate Description')
            ->call('save');

        $component->assertHasErrors('name');
    }
}