# Task Management Workflow

## Configuration
- **Target Task Duration**: 15 minutes (tasks should be broken down if expected to exceed this)
- **Last Task Number**: 120 (strictly updated after task creation, represents total tasks created)

This workflow defines how tasks are managed throughout the project lifecycle using the task tracking system in `./.claude/tasks.md`.

## Requirements and Architecture Management

### Requirements (`requirements.md`)
- User requests AI to add requirements based on prompts
- Requirements serve as the foundation for task generation
- User can request tasks to be generated from accumulated requirements

### Architecture (`architecture.md`)
- Contains user's architectural instructions for the software
- User may ask AI to add architectural guidance
- Should be referenced during implementation

### Tools (`tools.md`)
- Lists available tools and their command-line instructions
- When AI resolves tool call issues, solutions are documented here
- Prevents repeated struggles with the same tools
- **MANDATORY**: Must be consulted before using ANY commands (Docker, PHP, npm, testing, etc.)
- Contains the exact commands and patterns that must be used for this project

### Task Archive
- Completed tasks can be archived to `tasks-archive/tasks-archive-<yyyy-mm-dd>.md` files
- User can request archival of completed tasks
- Archive files are dated for organization and historical reference

## Task Statuses and Flow

### 1. Todo (Default)
- **When**: Initial status for new tasks
- **Set by**: AI when user requests a new task
- **Requirements**: Task description and basic requirements defined

### 2. Planned
- **When**: After AI has analyzed and planned the task in detail, AND reviewed it against current state
- **Requirements**: 
  - Implementation plan section completed with detailed implementation steps
  - Test plan section completed with verification steps broken down into numbered steps
  - Task broken down to fit within target duration (15 minutes)
  - **Pre-Implementation Review**: Task reviewed against:
    * Current requirements.md for any requirement changes
    * Current architecture.md for architectural alignment
    * State of implementation from completed previous tasks
    * Existing code/files that may have been created or modified
- **Transition from**: Todo

### 3. Started
- **When**: AI begins working on the task
- **Requirements**: 
  - Started timestamp must be set (local time via bash)
  - AI actively implementing the planned steps
  - Must review and follow requirements from `requirements.md`
  - Must follow architectural guidance from `architecture.md`
  - **Scope Expansion Protocol**: If task scope expands significantly during implementation (estimated to exceed twice the target duration), AI must:
    1. Pause implementation
    2. Document current progress and discovered scope expansion
    3. Propose breaking the task into focused sub-tasks
    4. Request user approval before continuing with expanded scope
- **Transition from**: Planned or Review (if corrections needed)

### 4. Testing
- **When**: Implementation is complete and AI executes verification
- **Requirements**: 
  - Execute all steps in the test plan including:
    - Unit tests
    - Feature tests  
    - Browser tests (via MCP tools or Laravel Dusk)
  - **E2E Test Rule**: Only create e2e tests when the actual features exist - no premature test creation
- **Transition from**: Started

### 5. Review
- **When**: Testing is complete and task is ready for user review
- **Requirements**: 
  - Review timestamp must be set (local time via bash)
  - All testing completed successfully
  - **Issues Summary**: Must include explicit "Issues Found" section listing any problems discovered during task and their fixes
- **Transition from**: Testing

### 6. Completed
- **When**: User confirms task completion after review
- **Requirements**: 
  - Only user can set this status
  - **STEP 1 - MARK COMPLETED**: Mark task as completed with timestamp and duration
    - Duration calculated from Started to Completed timestamps
  - **⚠️ STEP 2 - MANDATORY GIT CHECK ⚠️**: AI must run `git status` after marking as Completed
  - **🚨 STEP 3 - MANDATORY COMMIT 🚨**: If uncommitted changes exist, AI MUST commit changes
    - **NEVER SKIP**: This step cannot be skipped under any circumstances
    - Include task number and summary in commit message
    - Use standard commit format with Claude Code attribution
- **Transition from**: Review (user approval only)

## Technical Approach Philosophy

When implementing tasks, always prioritize:

1. **Clear Technical Architecture**: Choose solutions that are easy to understand and maintain
2. **Simple, Elegant Approach**: Favor straightforward implementations over complex ones
3. **Minimal Code Changes**: Achieve the goal with the least amount of code modification
4. **Existing Patterns**: Use established patterns and conventions already present in the codebase
5. **Future Maintainability**: Ensure changes don't create technical debt or complicate future modifications

Before starting implementation, think through:
- What is the root cause of the issue?
- What is the simplest way to address it?
- How can we solve this with minimal disruption to existing code?
- Does this approach align with existing architectural patterns?
- Will this solution be clear to future developers?

## Task Structure

Each task in `./claude/tasks.md` should contain:

- **Task Number**: Sequential number for easy reference (e.g., 1, 2, 3)
- **Title**: Clear, descriptive task name with checkbox `[ ]` (checked `[x]` when status is Completed)
- **Status**: Current status from the workflow above
- **Description**: Detailed task requirements
- **Implementation Plan**: Numbered implementation steps with checkboxes `[ ]` (checked `[x]` when step is completed)
- **Test Plan**: Numbered verification steps with checkboxes `[ ]` (checked `[x]` when step is completed)
- **Started**: Timestamp when work began
- **Review**: Timestamp when ready for user review
- **Completed**: Timestamp when user approved completion
- **Duration**: Calculated time from Started to Completed

## Referencing System

Tasks and steps can be referenced using the format `<task>.<step>`:
- Task reference: `1` (refers to task 1)
- Step reference: `1.3` (refers to step 3 of task 1)
- This allows precise communication about specific implementation steps

## AI Instructions

- Always check `./.claude/tasks.md` before starting work
- Use "Last Task Number" from this workflow file to determine next task number
- Increment and update "Last Task Number" ONLY after creating a new task
- Assign sequential task numbers (1, 2, 3, etc.) to new tasks
- Number all implementation and test plan steps for easy reference
- Use the `<task>.<step>` format when referencing specific steps
- **BEFORE moving from Todo to Started**: ALWAYS review task against current state:
  * Check `./.claude/requirements.md` for any requirement changes since task creation
  * Check `./.claude/architecture.md` for architectural alignment
  * Review what previous completed tasks have implemented/changed
  * Verify existing code/files haven't made steps obsolete
  * Update implementation and test steps if needed
- Review requirements in `./.claude/requirements.md` during implementation
- Follow architectural guidance in `./.claude/architecture.md`  
- **🚨 MANDATORY TOOL USAGE 🚨**: ALWAYS consult `./.claude/tools.md` FIRST before using ANY commands
  * NEVER run Docker commands without checking tools.md first
  * NEVER run PHP/Laravel commands without checking tools.md first  
  * NEVER run npm/node commands without checking tools.md first
  * NEVER run test commands without checking tools.md first
  * Use the EXACT commands documented in tools.md
  * This prevents wasting time and tokens on incorrect approaches
- Follow the status progression strictly
- Break down tasks that exceed 15-minute target duration
- Set timestamps using bash commands for local time (no timezone)
- Only users can mark tasks as Completed
- If user provides corrections during Review, return to Started status
- **🚨 CRITICAL COMMIT REQUIREMENT 🚨**: When user requests task approval (approve-task), AI MUST follow the mandatory completion checklist:
  1. Mark task as Completed with timestamp
  2. Run `git status` to check for changes
  3. Commit ALL changes if they exist - NEVER SKIP THIS STEP
  4. This helps maintain clean task-based commit history and prevents lost work
- Document all work in the appropriate task entry
- Document resolved tool issues in `tools.md`
- When user requests archival, move completed tasks to `tasks-archive/tasks-archive-<yyyy-mm-dd>.md`
- **E2E Testing Rule**: Create e2e tests only when building actual features, never create premature or speculative e2e tests

## Timestamp Format

Use local timestamps obtained via bash command:
```bash
date '+%Y-%m-%d %H:%M:%S'
```

## Task Archival

When user requests archival of completed tasks:

1. Create directory `tasks-archive/` if it doesn't exist
2. Create archive file `tasks-archive/tasks-archive-<yyyy-mm-dd>.md` using current date
3. Move all completed tasks from `tasks.md` to the archive file
4. Keep the task template and reference format in `tasks.md`
5. Archive file should include:
   - Date archived
   - List of archived tasks with all their details
   - Original task numbers preserved for reference