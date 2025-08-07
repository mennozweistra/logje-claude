# Software Requirements - Logje Health Tracker

This document contains the requirements for the Logje health tracking web application.

## Functional Requirements

### Core Features
- **Daily Measurement Tracking**: Users can record multiple daily health measurements including:
  - Blood glucose levels (mmol/L, realistic 0-12 range) with optional fasting indicator
  - Weight measurements (kg)
  - Exercise activities (description and duration in minutes)
  - Daily notes (text entries with timestamps)
  - Medication intake tracking (multiple medications per entry from predefined list)
  - Food consumption tracking with nutritional information (calories and carbohydrates)
- **Six Measurement Types**: Complete measurement system with Weight, Glucose, Medication, Food, Exercise, Notes
- **User-Specific Medication Database**: Each user maintains their own personal medication list
  - Medications are linked to specific users and not shared between accounts
  - Users can add, edit, and delete their own medications
  - Medications can only be deleted when no medication measurements reference them
  - Foreign key constraint protection prevents deletion of medications with existing measurements
- **Medication Seeding**: System includes seeder with common medications (Rybelsus, Metformine, Amlodipine, Kaliumlosartan, Atorvastatine) for development/testing purposes
- **Food Database**: Comprehensive food database with nutritional information per 100g
- **Notes Support**: All measurement types can include optional notes for additional context
- **Time-based Data Entry**: All measurements and notes include time of entry (24-hour format) with user-editable timestamps
- **Historical Data Management**: Users can add, edit, and delete measurements/notes for past dates (no future dates)
- **User Authentication**: Simple email/password authentication with secure session management
- **Data Persistence**: Permanent data retention with MySQL database
- **Cross-Platform Access**: Responsive web application for browsers and mobile devices
- **Personal Todo List**: Users can manage their own private todo items with status tracking and detailed views

### Data Entry Interface
- **Default View**: Today's date as default view with Dutch date format (dd-mm-yyyy)
- **Date Navigation**: Users can navigate to any past date for data entry/editing with previous/next buttons
- **Auto-population**: Current time pre-filled for new measurements (editable by user)
- **Time Format Requirements**: All time data entry and display must use military time (24-hour format), Dutch style
  - Time entry format: HH:MM (e.g., 14:30, 09:15, 23:45)
  - Time display format: HH:MM throughout the application
  - Default times must be based on user's local browser timezone, not UTC
  - No AM/PM format - strictly 24-hour military time format
- **Multiple Entries**: Support multiple measurements per type per day
- **Data Validation**: Prevent future date entries with realistic glucose ranges (0-12 mmol/L)
- **CRUD Operations**: Create, read, update, delete measurements for any past date
- **Responsive Design**: 6-column grid layout for measurement type buttons, mobile-optimized
- **Filtering System**: Collapsible measurement type filters for dashboard view
- **Time Preservation**: User-entered times are preserved during measurement creation and editing
- **UI Layout Consistency Requirements**: All page layouts must maintain consistent width alignment
  - Dashboard main content area width must match reports page width
  - Both dashboard and reports width must align with top logo and menu width
  - Modal dialogs on dashboard must not extend beyond the main dashboard content area width
  - All width consistency must be maintained across desktop and mobile viewports
  - Conduct mobile responsiveness testing to ensure consistent layout scaling
- **Icon Requirements**: Measurement type icons must be clear and distinguishable
  - Current weight and medicines icons need replacement with more appropriate alternatives
  - All icons should be intuitive and easily recognizable for their measurement types
  - Consider research into alternative icon libraries if current library lacks suitable options
  - Maintain consistent icon style and sizing across all measurement types

### Food Tracking Features

#### Food Database Management
- **User-Specific Food Database**: Each user has their own personal food database
  - Foods are linked to specific users and not shared between accounts
  - Users can only see and manage their own food items
- **Food Maintenance Interface**: Personal food database management functionality
  - Add new foods to personal database with name, description, calories per 100g, carbs per 100g
  - Edit existing food nutritional information for owned foods
  - Delete foods from personal database (only when no food measurements reference them)
  - Searchable list of all personal foods in database for management
  - Foreign key constraint protection: foods cannot be deleted if linked to existing measurements
- **Food Database**: Persistent database with foods and their per-100g nutritional information
  - Each food record contains: name, description (optional), calories per 100g, carbohydrates per 100g
  - Pre-seeded with common foods (fruits, vegetables, proteins, grains, etc.)
  - Searchable food database with auto-complete functionality for consumption tracking

#### Food Consumption Tracking
- **Food Entry System**: Users can log actual food consumption with:
  - Search and select foods from database (separate from food management)
  - Enter actual consumed amount in grams (customizable portions)
  - **Integer-Only Data Entry**: All food amounts must be entered as whole numbers (integers) for simplicity
    - Users enter portions like "150g" not "150.5g" - no decimal input required or allowed
    - UI validation ensures only whole number grams are accepted
    - Internal calculations may use floating point math, but user interface remains integer-based
  - Multiple foods per meal/entry with timestamp
  - Automatic calculation of total calories and carbohydrates based on actual portion size
  - Real-time nutritional information display during entry
- **Nutritional Calculations**: System automatically calculates:
  - Total calories consumed per entry based on actual portion sizes
  - Total carbohydrates consumed per entry based on actual portion sizes
  - Nutritional values displayed in dashboard for daily tracking
- **Food Consumption Workflow**: 
  1. User searches for food item from existing database (e.g., "steak")
  2. Selects food from search results (shows per-100g values as reference)
  3. Enters actual consumed amount in grams (e.g., 220g for steak with 100g reference values)
  4. System calculates actual calories/carbs (e.g., if steak is 250 cal/100g, 220g = 550 cal)
  5. User can add multiple foods to single meal entry
  6. Entry saves with timestamp and total nutritional information

#### Clear Separation of Concerns
- **Food Management**: Administrative function to maintain food database (calories/carbs per 100g)
- **Food Consumption**: User function to log actual food intake with custom portions
- **No Mixing**: Food database management is separate from daily food consumption tracking

### Healthy Day Indicator
- **Real-time Health Status**: Visual indicator on the Health screen showing whether the current day meets health compliance rules
- **Time-based Rule Engine**: Dynamic rule evaluation based on current local time for today, and complete rule evaluation for past dates
- **Visual Feedback System**: 
  - Happy smiley (😊) when all applicable rules for the current time are met
  - Sad smiley (😔) when one or more applicable rules are not met
  - Indicator positioned next to the day name on the Health screen
- **Interactive Rule Details**: Clicking the health indicator opens a modal popup showing:
  - Complete list of health rules for the day
  - Status of each rule (met/not met) with visual indicators
  - Time-based applicability for current day rules
- **Daily Health Rules** (time-sensitive activation for current day, all apply for past dates):
  - **09:00+**: Rybelsus medication taken (any time after rule activates)
  - **09:00+**: At least one fasting glucose measurement recorded for the day
  - **11:00+**: Metformine, Amlodipine, and Kaliumlosartan medications each taken at least once (can be separate or combined entries)
  - **13:00+**: At least two glucose measurements total recorded for the day (ensures second measurement exists)
  - **14:00+**: Exercise activity logged (any time after rule activates)
  - **18:00+**: At least three glucose measurements total recorded for the day (ensures third measurement exists)
  - **20:00+**: Second Metformine occurrence and Atorvastatine taken (Metformine taken twice total, Atorvastatine taken once)
- **Rule Evaluation Logic**:
  - **Rule Activation**: Time thresholds indicate when rules become active for evaluation, not deadlines
  - **Medication Counting**: System tracks medication occurrence counts (first vs second Metformine intake)
  - **Late Compliance**: Medications/measurements taken after rule activation time still satisfy the rule
  - **Current Day**: Only rules with activation times that have passed are evaluated
  - **Past Days**: All seven rules must be met for the day to be considered healthy
  - **Future Days**: Not applicable (no future date entry allowed)
  - **Daily Reset**: Rules reset at midnight for new day evaluation
- **Rule Status Modal Design**:
  - **Simple Layout**: Clean list of all daily rules with clear visual indicators
  - **Inactive Rules**: Rules not yet activated (time hasn't passed) shown greyed out
  - **Met Rules**: Satisfied rules displayed with green checkmark ✓
  - **Failed Rules**: Active but unmet rules displayed with red cross ✗
  - **Real-time Updates**: Modal reflects current status when opened

### Reporting Features
- **Progress Tracking**: Visual reports showing measurement trends over time with Chart.js
- **Nutritional Tracking**: Daily calorie and carbohydrate consumption charts with date range selection
- **Historical Analysis**: Comprehensive data visualization with multiple chart types:
  - Glucose data charts with trend analysis
  - Weight tracking charts with progress visualization
  - Exercise activity charts with duration tracking
  - Nutrition charts with daily calorie and carbohydrate consumption
- **Data Export**: CSV and PDF export functionality for all measurement data
- **Interactive Charts**: Responsive charts with date range selection and filtering

### User Management
- Single user accounts (no family/shared accounts)
- Simple email/password authentication with CSRF protection
- Secure session management with extended lifetime (8 hours)
- User registration temporarily disabled (can be re-enabled)
- Production-ready authentication with anti-caching headers

## Non-Functional Requirements

### Performance
- Fast loading times on mobile and desktop with optimized asset loading
- Responsive design for various screen sizes with mobile-first approach
- **Progressive Web App (PWA)**: Fully installable PWA with:
  - Complete manifest.json with proper metadata and PNG icons
  - Service worker for caching and offline capability
  - Cross-browser compatibility (Chrome/Brave install prompts, Safari manual installation)
  - Standalone display mode for native app experience
- Optimized database queries and efficient Laravel Livewire components

### Security
- Secure user authentication with Laravel's built-in security features
- CSRF protection and secure session management
- Data privacy protection for health information
- HTTPS enforcement in production
- Secure cookie configuration with SameSite protection
- Anti-caching headers for authentication pages
- Permanent data retention with backup considerations

### Usability
- Intuitive interface for daily data entry with visual icons and clear layout
- Mobile-friendly responsive design with 6-column grid on desktop, 3-column on mobile
- 24-hour time format with user-editable timestamps
- Metric units only (kg for weight, mmol/L for glucose with realistic ranges)
- Dutch date format (dd-mm-yyyy) throughout the application
- Consistent color scheme with health-themed icons (green apple for food, etc.)
- Collapsible filters to reduce visual clutter
- Clear visual feedback for user actions

#### Mobile UI Optimization
- **Compact Mobile Layout**: Mobile interface uses compact design with minimal spacing between UI elements to maximize content display
- **Desktop Spacious Layout**: Desktop interface can use more generous spacing and larger elements for comfortable viewing
- **Minimized Mobile Header**: Header size reduced on mobile devices to preserve screen real estate for content
- **Measurement-Focused Dashboard**: Dashboard prioritizes display of measurement entries over creation controls
- **Repositioned Creation Controls**: Measurement creation buttons moved below measurement entries on mobile to reduce visual prominence and space usage
- **Touch-Friendly Targets**: Maintain appropriate touch target sizes (minimum 44px) while using compact layouts
- **Portrait Mode Optimization**: In portrait orientation, less critical details (such as measurement type labels) may use smaller fonts or be visually de-emphasized
- **Swipe Date Navigation** *(nice-to-have)*: Enable swipe gestures for date navigation on mobile devices

#### User Story: Mobile Dashboard Experience
**As a** mobile user reviewing my daily measurements  
**I want** a compact, focused dashboard interface  
**So that** I can quickly view my measurement data without excessive scrolling or visual clutter  

**Acceptance Criteria:**
- Mobile dashboard displays measurements prominently with minimal spacing
- Header takes up minimal vertical space on mobile screens
- Creation buttons are positioned below measurement entries, not prominently at the top
- Touch targets remain easily tappable (minimum 44x44px)
- Less important details (like measurement type labels) use appropriately smaller fonts in portrait mode
- Desktop layout maintains current spacious design for comfortable desktop use
- No information is permanently hidden - all current functionality remains accessible

### Medication Management Features

#### User-Specific Medication Database
- **Personal Medication List**: Each user maintains their own private medication database
  - Medications are linked to specific users and not shared between accounts
  - Users can only access and manage their own medication records
- **Medication CRUD Operations**: Complete medication management functionality
  - **Create**: Users can add new medications to their personal list with name and description
  - **Read**: Users can view all their medications in a searchable, manageable list
  - **Update**: Users can edit medication names and descriptions for their owned medications
  - **Delete**: Users can delete medications from their personal list with safety constraints
- **Foreign Key Protection**: Medications cannot be deleted if referenced by existing measurements
  - System prevents deletion of medications that have associated medication measurements
  - Clear error messages when deletion is blocked due to existing references
  - Foreign key constraints maintain data integrity automatically

### Todo List Features

#### Personal Todo Management
- **Private Todo Lists**: Each user maintains their own personal todo list
  - Todos are linked to specific users and not shared between accounts
  - Users can only access and manage their own todo items
  - No limit on total number of todos per user
- **Todo Data Structure**: Each todo contains:
  - **Title**: Required field for todo identification
  - **Description**: Optional field for additional context
  - **Priority**: Required field with levels (High, Medium, Low)
  - **Status**: Current todo status (see Status Management below)
  - **Archive Flag**: Todos can be archived to hide from UI without deletion
- **Todo CRUD Operations**: Complete todo management functionality
  - **Create**: Users can add new todo items with title, optional description, and priority
  - **Read**: Users can view all their active (non-archived) todos in a list view with status filtering
  - **Update**: Users can edit todo titles, descriptions, priority, and change status
  - **Delete**: Users can delete todo items from their personal list
  - **Archive**: Users can archive todos to remove from active view without permanent deletion

#### Todo Status Management
- **Four Status Types**: Todos support comprehensive status tracking
  - **Todo**: Initial status for new todo items (pending/not started)
  - **Ongoing**: Todo items currently being worked on or in progress
  - **Paused**: Todo items that are temporarily halted or on hold
  - **Done**: Completed todo items
- **Status Transitions**: Users can change todo status at any time with no validation restrictions
- **Status Filtering**: List view includes collapsible status filters similar to dashboard measurement filters
- **Default Sorting**: Todos are sorted by status priority: Ongoing → Paused → Todo → Done
- **Secondary Sorting**: Within each status group, todos are sorted by priority (High → Medium → Low)

#### Todo Detail View
- **Detailed Todo View**: Each todo can be opened in a dedicated detail view
- **Notes System**: Todo detail view includes comprehensive notes functionality
  - Users can add multiple notes to any todo item
  - Notes are timestamped and listed with most recent first (chronological order)
  - **Note Content**: Plain text only, maximum 1000 characters per note
  - **Note Management**: Notes can be edited and deleted after creation
  - Notes support progress updates, reminders, or additional context
- **Navigation**: Easy navigation between list view and detail view

#### Navigation Integration
- **Menu Structure Update**: Main navigation updated to include todo functionality
  - **Health**: Renamed from "Dashboard" - health measurement tracking (existing functionality)
  - **Todo**: New menu item for todo list functionality (completely separate from health tracking)
  - **Reports**: Existing reports functionality (unchanged)
- **Menu Order**: Health → Todo → Reports in top navigation
- **Separation of Concerns**: Todo functionality remains completely independent from health tracking features

## Technical Requirements

### Technology Stack
- **Backend**: Laravel PHP framework (v12.x) with PHP 8.3
- **Frontend**: Laravel Livewire for dynamic interactions, Tailwind CSS for styling
- **Database**: MySQL in production, Docker MySQL for local development
- **Charts**: Chart.js for data visualization and reporting
- **PWA**: Manifest.json, Service Worker, proper icon assets
- **Deployment**: CapRover server with webhook-based CI/CD from GitHub
- **Development**: Docker development environment with proper user permissions
- **Standards**: Follow Laravel coding standards and architectural practices

### Testing Requirements
- **Unit Tests**: All functions must have unit test coverage using Pest
- **Feature Tests**: Application features must be feature tested using Pest (food tracking, medication, etc.)
- **End-to-End Tests**: Complete user workflows tested with Laravel Dusk and Playwright MCP
- **Specific Test Coverage**: Measurement time preservation, glucose validation, PWA functionality
- **AI Testability**: Tests must be executable by AI systems via Docker
- **Local Testing**: Docker environment for consistent testing with proper permissions
- **Production Testing**: Playwright browser testing for production verification

### Development Standards
- Follow Laravel best practices and architectural patterns
- Code quality and maintainability focus
- Proper Docker development workflow with user permission management
- Multi-stage production Dockerfile with security hardening
- Automated deployment with CapRover and GitHub webhooks
- Environment-specific configuration management
- Comprehensive error handling and logging

### Deployment Requirements
- **Production Server**: CapRover deployment on server.logje.nl
- **Domain Configuration**: Primary domain logje.nl with www.logje.nl redirect
- **SSL/HTTPS**: Automatic SSL certificate management and HTTPS enforcement
- **Database**: MariaDB container integration with automated migrations
- **Auto-deployment**: GitHub webhook integration for automatic deployment on main branch commits
- **Environment Management**: Secure environment variable management through CapRover
- **Multi-stage Build**: Optimized production Docker container with security hardening
- **Asset Optimization**: Automated Vite asset compilation and deployment
- **Health Checks**: Container health monitoring and automatic restart capabilities

## User Stories

### As a health-conscious user:
- I want to log multiple daily measurements (glucose, weight, exercise, medication, food, notes) with timestamps
- I want to track my medication intake by selecting from a predefined list of my medications
- I want to log food consumption with automatic calorie and carbohydrate calculations
- I want to add daily notes to record thoughts, observations, or context for any date
- I want to add optional notes to any measurement for additional context
- I want to mark my fasting glucose readings to distinguish them from regular readings
- I want to record exercise with description and duration for activity tracking
- I want to view and edit historical data to correct past entries or add missed measurements
- I want to see my data organized by date with today as the default view in Dutch format
- I want to navigate to any past date to review or modify my measurements and notes
- I want my measurement times pre-filled with current time but editable if needed
- I want to see visual reports of my progress over time with interactive charts
- I want to export my data in CSV and PDF formats for sharing with healthcare providers
- I want to filter my dashboard view to focus on specific measurement types
- I want my health data to be secure and permanently stored
- I want the interface to work well on both desktop and mobile devices
- I want to install the app on my phone as a Progressive Web App for quick access

### As a system user:
- I want to login securely to access only my personal health data (registration temporarily disabled)
- I want my sessions to remain active for reasonable periods without frequent re-login
- I want the system to prevent me from entering future dates to maintain data integrity
- I want to use metric units consistently (kg for weight, mmol/L for glucose in realistic ranges)
- I want the system to preserve my custom time entries when creating or editing measurements
- I want the application to work reliably across different browsers (Chrome, Safari, Brave)
- I want the PWA to install correctly on my mobile device for offline-like access

### As a food tracking user:
- I want to search for foods from a comprehensive database when logging consumption
- I want to enter the actual amount I consumed in grams for accurate nutrition tracking
- I want to see automatic calculations of calories and carbohydrates based on my portion size
- I want to manage the food database by adding new foods with nutritional information
- I want to see daily nutrition totals and trends in visual charts

### As a health compliance user:
- I want to see at a glance whether I'm having a healthy day based on my medication and measurement schedule
- I want the healthy day indicator to show different status based on the current time (only checking rules that should have been completed by now)
- I want to click on the health status indicator to see detailed information about which rules I've met and which I haven't
- I want to see a happy smiley when I'm on track with my health routine and a sad smiley when I'm behind
- I want to see complete rule compliance status for past dates (all rules must be met for past days to be considered healthy)
- I want the system to track my specific medication schedule (Rybelsus, Metformine, Amlodipine, Kaliumlosartan, Atorvastatine) and glucose measurement timing requirements

---

*Requirements are managed through the workflow defined in `./.claude/workflow.md`*