<div class="space-y-6">
    {{-- Notes Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">
            Notes
            @if($this->notes->count() > 0)
                <span class="text-sm font-normal text-gray-500 ml-2">({{ $this->notes->count() }})</span>
            @endif
        </h3>
    </div>

    {{-- Add New Note Form --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <form wire:submit.prevent="addNote" class="space-y-3">
            <div>
                <label for="newNote" class="block text-sm font-medium text-gray-700 mb-1">
                    Add a note
                </label>
                <textarea 
                    id="newNote"
                    wire:model="newNote" 
                    rows="3"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('newNote') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                    placeholder="Add your note here..."
                    maxlength="500"
                ></textarea>
                @error('newNote')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <div class="flex justify-between items-center mt-1">
                    <p class="text-gray-500 text-xs">Maximum 500 characters</p>
                    <p class="text-gray-400 text-xs">{{ strlen($newNote) }}/500</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    wire:loading.attr="disabled"
                    wire:target="addNote"
                >
                    <span wire:loading.remove wire:target="addNote">Add Note</span>
                    <span wire:loading wire:target="addNote" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Adding...
                    </span>
                </button>
            </div>
        </form>
    </div>

    {{-- Notes List --}}
    @if($this->notes->count() > 0)
        <div class="space-y-3">
            @foreach($this->notes as $note)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    @if($editingNoteId === $note->id)
                        {{-- Edit Mode --}}
                        <form wire:submit.prevent="updateNote" class="space-y-3">
                            <div>
                                <textarea 
                                    wire:model="editingNoteContent" 
                                    rows="3"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('editingNoteContent') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    maxlength="500"
                                ></textarea>
                                @error('editingNoteContent')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-gray-500 text-xs">Maximum 500 characters</p>
                                    <p class="text-gray-400 text-xs">{{ strlen($editingNoteContent) }}/500</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded text-xs font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    wire:loading.attr="disabled"
                                    wire:target="updateNote"
                                >
                                    <span wire:loading.remove wire:target="updateNote">Save</span>
                                    <span wire:loading wire:target="updateNote">Saving...</span>
                                </button>
                                <button 
                                    type="button"
                                    wire:click="cancelEdit"
                                    class="inline-flex items-center px-3 py-1 bg-gray-300 border border-transparent rounded text-xs font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- View Mode --}}
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-900 text-sm whitespace-pre-wrap break-words">{{ $note->content }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    {{ $note->created_at->format('M j, Y \a\t g:i A') }}
                                    @if($note->updated_at->gt($note->created_at))
                                        <span class="text-gray-400">(edited {{ $note->updated_at->format('M j, Y \a\t g:i A') }})</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-3 flex-shrink-0">
                                <button 
                                    wire:click="editNote({{ $note->id }})"
                                    class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors duration-200"
                                    title="Edit note"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                <button 
                                    wire:click="deleteNote({{ $note->id }})"
                                    wire:confirm="Are you sure you want to delete this note? This action cannot be undone."
                                    class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors duration-200"
                                    title="Delete note"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-8">
            <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            
            <h3 class="text-sm font-medium text-gray-900 mb-2">No notes yet</h3>
            <p class="text-sm text-gray-500">Add your first note to keep track of progress, ideas, or important details.</p>
        </div>
    @endif
</div>