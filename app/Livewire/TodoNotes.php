<?php

namespace App\Livewire;

use App\Models\Todo;
use App\Models\TodoNote;
use Livewire\Component;

class TodoNotes extends Component
{
    public Todo $todo;
    public $newNote = '';
    public $editingNoteId = null;
    public $editingNoteContent = '';
    
    protected $rules = [
        'newNote' => ['required', 'string', 'min:1', 'max:500'],
        'editingNoteContent' => ['required', 'string', 'min:1', 'max:500'],
    ];
    
    protected $validationAttributes = [
        'newNote' => 'note',
        'editingNoteContent' => 'note',
    ];
    
    public function mount(Todo $todo)
    {
        // Ensure user can only access their own todo's notes
        if ($todo->user_id !== auth()->id()) {
            abort(403);
        }
        
        $this->todo = $todo;
    }
    
    /**
     * Add a new note
     */
    public function addNote()
    {
        $this->validate(['newNote' => $this->rules['newNote']]);
        
        try {
            TodoNote::create([
                'todo_id' => $this->todo->id,
                'content' => trim($this->newNote),
            ]);
            
            $this->newNote = '';
            $this->dispatch('note-added', 'Note added successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('note-error', 'Failed to add note.');
        }
    }
    
    /**
     * Start editing a note
     */
    public function editNote($noteId)
    {
        $note = TodoNote::where('todo_id', $this->todo->id)->findOrFail($noteId);
        
        $this->editingNoteId = $noteId;
        $this->editingNoteContent = $note->content;
    }
    
    /**
     * Update the note being edited
     */
    public function updateNote()
    {
        $this->validate(['editingNoteContent' => $this->rules['editingNoteContent']]);
        
        try {
            $note = TodoNote::where('todo_id', $this->todo->id)->findOrFail($this->editingNoteId);
            $note->update([
                'content' => trim($this->editingNoteContent),
            ]);
            
            $this->cancelEdit();
            $this->dispatch('note-updated', 'Note updated successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('note-error', 'Failed to update note.');
        }
    }
    
    /**
     * Cancel editing
     */
    public function cancelEdit()
    {
        $this->editingNoteId = null;
        $this->editingNoteContent = '';
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($noteId)
    {
        try {
            $note = TodoNote::where('todo_id', $this->todo->id)->findOrFail($noteId);
            $note->delete();
            
            $this->dispatch('note-deleted', 'Note deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('note-error', 'Failed to delete note.');
        }
    }
    
    /**
     * Get notes in chronological order (newest first)
     */
    public function getNotesProperty()
    {
        return $this->todo->notes()->recentFirst()->get();
    }
    
    public function render()
    {
        return view('livewire.todo-notes');
    }
}