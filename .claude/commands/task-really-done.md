# Task Really Done Command

Verify and test the current task (from recent context) that was just reported as ready for review.

**FIRST**: Carefully verify all work completed for the current task by:

1. **Code Review**: Review all modified files to ensure implementation is complete and correct
2. **Test Verification**: Run ALL unit and feature tests to ensure nothing is broken
3. **Fix Any Issues**: If tests fail, identify and fix the problems before proceeding

**THEN**: Ensure the development environment is properly set up by:

4. **Docker Environment**: Ensure Docker containers are running for local development
5. **Database Setup**: Run the complete database seeder to populate test data
6. **Verify Setup**: Confirm the application is accessible and functional

**FINALLY**: Provide the user with:

7. **Test Results**: Summary of all test results (passing/failing counts)
8. **Local Testing URL**: The URL for testing the application locally
9. **Test Command**: The exact command to run all tests

**IMPORTANT**: This command should only be used when you believe the task is genuinely complete. If any tests fail or issues are found, fix them before reporting completion. Do not report completion until everything is actually working correctly.