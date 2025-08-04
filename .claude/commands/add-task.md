# Add Task Command

Add a new task to the project task list based on user input, following the workflow defined in `./.claude/workflow.md`.

**FIRST**: Read and understand the current project context by reviewing:
- `./.claude/workflow.md` - Task management standards and format requirements
- `./.claude/tasks.md` - Existing tasks to understand numbering and avoid duplicates
- `./.claude/requirements.md` - Current requirements to understand project scope

**THEN**: Analyze the user's input to determine if this is actually a task or a requirement:

## Input Analysis

### Is this a Task or Requirement?
**Task Indicators:**
- Specific implementation work (code changes, bug fixes, feature implementation)
- Has clear deliverables and completion criteria
- Can be completed within target duration (break it down if needed)
- Involves actual development work
- References existing requirements for implementation

**Requirement Indicators:**
- Describes what the system should do (not how to implement it)
- Defines user needs or business rules
- Lacks specific implementation details
- Focuses on "what" rather than "how"
- Would benefit from user stories and acceptance criteria

### If it's a Requirement:
Ask: "This sounds more like a requirement. Would you like me to add this as a requirement using the add-requirements command instead?"

## Task Creation Process

### If it's confirmed as a Task:

1. **Ask Clarifying Questions** if the input is unclear:
   - What specific work needs to be done?
   - What are the expected deliverables?
   - Are there dependencies on other tasks or requirements?
   - What is the scope and complexity?
   - How should completion be verified?

2. **Assess Task Size**:
   - If the task seems larger target duration, suggest breaking it down
   - Ask for specific subtasks or implementation steps
   - Ensure each task has a clear, achievable scope

3. **Determine Task Details**:
   - Priority level (high/medium/low based on dependencies and urgency)
   - Dependencies on other tasks
   - Required requirements alignment
   - Implementation approach
   - Test strategy

4. **Create Comprehensive Task** :
   - Follow the workflow format

