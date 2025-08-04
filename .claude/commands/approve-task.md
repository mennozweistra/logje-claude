# Task Approved Command

Commit the current task's changes if needed. Mark the current task (from recent context) as completed following the task completion workflow defined in `./.claude/workflow.md`.

**If it's unclear which task to complete**: State that the task to approve is unclear and ask for clarification.

**Otherwise**: Execute the task completion process by following ALL requirements from `./.claude/workflow.md` for moving a task from Review status to Completed status, including:

- Proper timestamp and duration calculation  
- Task status updates and checkbox marking

Provide a brief completion summary when finished.