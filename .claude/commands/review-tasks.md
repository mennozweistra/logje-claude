# Task Review and Alignment

Perform a comprehensive review of all incomplete tasks to ensure quality and alignment with project requirements.

## Review Process:

1. **Read Project Requirements**
   - Review `./.claude/requirements.md` to understand current project requirements
   - Note any recent changes or additions to requirements

2. **Identify Incomplete Tasks** 
   - Review `./.claude/tasks.md` to identify all tasks with status: Todo, Planned, Started, Testing, or Review
   - List each incomplete task with its current status

3. **Requirements Alignment Check**
   - For each incomplete task, verify it aligns with and fulfills specific requirements from requirements.md
   - Flag any tasks that seem outdated, redundant, or misaligned with current requirements
   - Identify any missing tasks needed to fulfill requirements

4. **Implementation Plan Review**
   - Ensure each task has a detailed, actionable implementation plan with specific steps
   - Verify steps are properly ordered with clear dependencies
   - Check that complex tasks are broken down into manageable 15-minute segments
   - Ensure each step is specific enough to be actionable (avoid vague descriptions)

5. **Test Plan Quality Assurance**
   - Verify each task has a comprehensive test plan with both manual and automated tests
   - For backend functionality: Ensure unit tests and feature tests using Pest PHP are specified
   - For frontend functionality: Ensure feature tests and browser tests are specified  
   - For database changes: Ensure migration tests and data integrity tests are specified
   - Check that test files are given specific paths (e.g., `tests/Feature/TaskName/TestName.php`)
   - Ensure test coverage includes edge cases, error conditions, and user scenarios

6. **Dependencies and Task Order**
   - Verify task dependencies are clearly marked and correct
   - Ensure tasks are ordered logically based on dependencies
   - Flag any circular dependencies or missing prerequisite tasks

## Output Format:

Provide a structured report with:
- **Summary**: Overview of total tasks reviewed and key findings
- **Requirements Alignment Issues**: Any tasks that don't align with requirements
- **Implementation Plan Issues**: Tasks needing better breakdown or clearer steps  
- **Test Plan Issues**: Tasks missing adequate test coverage
- **Dependency Issues**: Any dependency or ordering problems
- **Recommendations**: Specific actions to improve tasks

Be thorough and systematic in this review.