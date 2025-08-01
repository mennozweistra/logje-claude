# Software Requirements - Logje Health Tracker

This document contains the requirements for the Logje health tracking web application.

## Functional Requirements

### Core Features
- **Daily Measurement Tracking**: Users can record multiple daily health measurements including:
  - Blood glucose levels (mmol/L) with optional fasting indicator
  - Weight measurements (kg)
  - Exercise activities (description and duration in minutes)
  - Daily notes (text entries with timestamps)
- **Notes Support**: All measurement types can include optional notes for additional context
- **Time-based Data Entry**: All measurements and notes include time of entry (24-hour format)
- **Historical Data Management**: Users can add, edit, and delete measurements/notes for past dates (no future dates)
- **User Authentication**: Simple email/password authentication
- **Data Persistence**: Permanent data retention with MySQL database
- **Cross-Platform Access**: Responsive web application for browsers and mobile devices

### Data Entry Interface
- **Default View**: Today's date as default view
- **Date Navigation**: Users can navigate to any past date for data entry/editing
- **Auto-population**: Current time pre-filled for new measurements
- **Multiple Entries**: Support multiple measurements per type per day
- **Data Validation**: Prevent future date entries
- **CRUD Operations**: Create, read, update, delete measurements for any past date

### Reporting Features
- **Progress Tracking**: Visual reports showing measurement trends over time
- **Historical Analysis**: Long-term data visualization (specifications to be defined later)

### User Management
- Single user accounts (no family/shared accounts)
- Simple email/password authentication
- User registration and login system

## Non-Functional Requirements

### Performance
- Fast loading times on mobile and desktop
- Responsive design for various screen sizes
- Online-only operation (no offline support required)

### Security
- Secure user authentication
- Data privacy protection for health information
- Permanent data retention

### Usability
- Intuitive interface for daily data entry
- Mobile-friendly responsive design
- 24-hour time format
- Metric units only (kg for weight, mmol/L for glucose)

## Technical Requirements

### Technology Stack
- **Backend**: Laravel PHP framework (Latest LTS)
- **Frontend**: Laravel Livewire for dynamic interactions
- **Database**: MySQL with Docker for local development
- **Deployment**: Caprover server with webhook-based CI/CD
- **Standards**: Follow Laravel coding standards and architectural practices

### Testing Requirements
- **Unit Tests**: All functions must have unit test coverage using Pest
- **Feature Tests**: Application features must be feature tested using Pest
- **End-to-End Tests**: Complete user workflows tested with Laravel Dusk and Playwright
- **AI Testability**: Tests must be executable by AI systems
- **Local Testing**: Docker environment for consistent testing

### Development Standards
- Follow Laravel best practices
- Adhere to Laravel architectural patterns
- Code quality and maintainability focus

## User Stories

### As a health-conscious user:
- I want to log multiple daily measurements (glucose, weight, exercise) with timestamps
- I want to add daily notes to record thoughts, observations, or context for any date
- I want to add optional notes to any measurement for additional context
- I want to mark my fasting glucose readings to distinguish them from regular readings
- I want to record exercise with description and duration for activity tracking
- I want to view and edit historical data to correct past entries or add missed measurements
- I want to see my data organized by date with today as the default view
- I want to navigate to any past date to review or modify my measurements and notes
- I want my measurement times pre-filled with current time for quick entry
- I want to see visual reports of my progress over time
- I want my health data to be secure and permanently stored
- I want the interface to work well on both desktop and mobile devices

### As a system user:
- I want to register with email and password for secure access
- I want to login securely to access only my personal health data
- I want the system to prevent me from entering future dates to maintain data integrity
- I want to use metric units consistently (kg for weight, mmol/L for glucose)

---

*Requirements are managed through the workflow defined in `./.claude/workflow.md`*