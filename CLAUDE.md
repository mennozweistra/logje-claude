# Project Instructions for Claude

## Task Management

**CRITICAL**: At the start of EVERY new conversation session, IMMEDIATELY:
1. Check `./.claude/tasks.md` for existing tasks
2. If open tasks exist, print the task and current step with proper formatting:
   - Use clear section headers
   - Include line breaks between sections
   - Format status information clearly
3. If there are no open tasks, state "No tasks are planned currently"

**IMPORTANT**: Always follow the task management workflow defined in `./.claude/workflow.md`.

### Key Files:
- `./.claude/workflow.md` - Complete workflow definition (contains Last Task Number)
- `./.claude/tasks.md` - Active task tracking
- `./.claude/requirements.md` - Software requirements
- `./.claude/architecture.md` - Architectural guidance
- `./.claude/tools.md` - Available tools and resolved issues
- `./.claude/tasks-archive/` - Archived completed tasks by date

### Before starting any work:
1. Check `./.claude/tasks.md` for existing tasks
2. If open tasks exist, print the task and current step with proper formatting:
   - Use clear section headers
   - Include line breaks between sections
   - Format status information clearly
3. If there are no open tasks, state "No tasks are planned currently"
4. Use "Last Task Number" from workflow.md for new task numbering
5. Review `./.claude/requirements.md` for project requirements
6. Follow architectural guidance in `./.claude/architecture.md`
7. Consult `./.claude/tools.md` for tool usage patterns
8. Follow the defined status progression: Todo → Planned → Started → Testing → Review → Completed
9. Break down tasks that exceed 15-minute target duration
10. Set appropriate timestamps when changing statuses
11. Only users can mark tasks as Completed
12. Archive completed tasks when requested by user

Refer to `./.claude/workflow.md` for complete workflow details and requirements.