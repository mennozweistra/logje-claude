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
7. **🚨 MANDATORY: Always consult `./.claude/tools.md` FIRST for ALL command usage 🚨**
   - NEVER run commands without checking tools.md first
   - Use the exact commands documented there
   - This prevents wasting time and tokens on incorrect approaches
   - Applies to Docker, PHP, Laravel, npm, testing, and all other commands
8. Follow the defined status progression: Todo → Planned → Started → Testing → Review → Completed
9. Break down tasks that exceed 15-minute target duration
10. Set appropriate timestamps when changing statuses
11. Only users can mark tasks as Completed
12. Archive completed tasks when requested by user

Refer to `./.claude/workflow.md` for complete workflow details and requirements.

## Named Commands System

**IMPORTANT**: Use the commands defined in `./.claude/commands/` directory for common operations.

When the user asks to "Run [command-name]", read the corresponding `./.claude/commands/[command-name].md` file and execute the prompt contained within it.

Examples:
- "Run review-tasks" → Read `./.claude/commands/review-tasks.md` and execute that prompt

Always check the specific command file in `./.claude/commands/` for the exact prompt before executing.

## Testing Credentials

**Production URL**: https://logje.nl
- **Email**: test@example.com
- **Password**: f~s!6hfi}BnLtEE

**Development Machine**: Local development
- **Email**: test@example.com
- **Password**: password

Note: logje.server.logje.nl is the CapRover internal URL, not a separate environment.

Use these credentials for testing login functionality and deployments.