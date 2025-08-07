# Task Approved Command

Mark the current task (from recent context) as completed following the task completion workflow defined in `./.claude/workflow.md`.

**If it's unclear which task to complete**: State that the task to approve is unclear and ask for clarification.

**Otherwise**: Execute the task completion process using this MANDATORY checklist:

## ⚠️ MANDATORY TASK COMPLETION CHECKLIST ⚠️

**Follow this checklist in order - DO NOT SKIP ANY STEPS:**

- [ ] **1. Update Task Status**: Mark task as Completed with timestamp and duration calculation
- [ ] **2. Update Task Checkboxes**: Mark main task checkbox as `[x]` completed
- [ ] **3. Run Git Status**: Execute `git status` to check for uncommitted changes
- [ ] **4. Commit Changes**: If changes exist, commit ALL changes with descriptive message including task number and summary
- [ ] **5. Provide Summary**: Give brief completion summary to user

**🚨 CRITICAL: Steps 3-4 (Git Status + Commit) are MANDATORY and cannot be skipped 🚨**

Follow ALL requirements from `./.claude/workflow.md` for proper task completion.