# Software Architecture - Logje Health Tracker

This document contains architectural instructions and guidance for the Logje health tracking application.

## System Architecture

### High-Level Architecture
- **Monolithic Laravel Application**: Single-application architecture using Laravel framework
- **Component-Based Architecture**: Livewire components handle both view and interaction logic
- **Repository Pattern**: Data access abstraction layer with service classes
- **Flux Architecture**: State management for complex component interactions
- **Online-Only Application**: No offline support required, simplified architecture
- **Responsive Web Design**: Single codebase for desktop and mobile browsers

### Application Layers
1. **Presentation Layer**: Livewire components with Blade templates
2. **Business Logic Layer**: Service classes and Livewire component logic
3. **Data Access Layer**: Repository classes with Eloquent ORM
4. **Database Layer**: MySQL database

## Technology Stack

### Backend Framework
- **Laravel (Latest LTS)**: PHP framework for rapid development
- **PHP 8.1+**: Modern PHP features and performance

### Frontend Technology
- **Laravel Livewire**: Component-based interfaces with server-side rendering
- **Livewire Flux**: Centralized state management for cross-component data flow
- **Livewire Volt**: Single-file components with functional PHP syntax
- **Blade Templates**: Laravel's templating engine for component views
- **Tailwind CSS**: Utility-first CSS framework for responsive design
- **Alpine.js**: Minimal JavaScript for enhanced interactions

### Responsive Design Breakpoints
- **Primary breakpoint**: `md` at 840px (custom override in tailwind.config.js)
- **Mobile Layout**: < 840px width (no prefix required)
- **Desktop Layout**: ≥ 840px width (use `md:` prefix)
- **Additional breakpoints**: `sm` (640px), `lg` (1024px), `xl` (1280px), `2xl` (1536px) remain available
- **Design Intent**: Primary mobile/desktop split at 840px, with legacy breakpoints preserved for existing code

### Database
- **MySQL**: Relational database for data persistence
- **Docker**: Local development environment with MySQL container
- **Laravel Migrations**: Database schema version control
- **Eloquent ORM**: Database abstraction and relationships

### Development Environment
- **Docker Compose**: Local development stack (Laravel + MySQL)
- **Caprover Deployment**: Production deployment with webhook CI/CD
- **Version Control**: Git-based workflow with main branch deployment

## Design Patterns

### Laravel Patterns
- **Repository Pattern**: Data access layer with concrete classes (no interfaces for YAGNI compliance)
- **Service Layer Pattern**: Business logic encapsulation
- **Observer Pattern**: Model events and listeners
- **Factory Pattern**: Model factories for testing
- **Command Pattern**: Artisan commands for batch operations

### Livewire Patterns
- **Component-Based Architecture**: Traditional class-based and Volt functional components
- **Flux State Management**: Centralized store for shared application state
- **Event-Driven Communication**: Component-to-component communication via Flux
- **Single-File Components**: Volt components for rapid development
- **Reactive Updates**: Server-side state with real-time UI synchronization

## Component Structure

### Laravel Directory Structure
```
app/
├── Http/
│   ├── Controllers/        # HTTP request handling (minimal with Livewire)
│   ├── Livewire/          # Livewire components
│   └── Middleware/        # Request middleware
├── Models/                # Eloquent models
├── Services/              # Business logic services
├── Repositories/          # Data access repositories (concrete classes)
└── Observers/             # Model observers
```

### Livewire Components Organization
- **Page Components**: Full-page Livewire components
- **Partial Components**: Reusable UI components
- **Form Components**: Form handling components
- **Chart Components**: Data visualization components

## Data Architecture

### Database Design Principles
- **Normalized Design**: Reduce data redundancy
- **Foreign Key Constraints**: Maintain referential integrity
- **Indexed Columns**: Optimize query performance
- **Soft Deletes**: Preserve data integrity

### Core Entities
- **Users**: Authentication and profile data (email/password)
- **Measurements**: Daily health measurement records with timestamps and optional notes
- **Measurement Types**: Predefined categories (glucose, weight, exercise, notes)
- **Daily Notes**: Date-based notes with timestamps (treated as measurements without values)

### Specific Data Models
- **Glucose Measurements**: Value (mmol/L), timestamp, optional fasting indicator, optional note
- **Weight Measurements**: Value (kg), timestamp, optional note
- **Exercise Measurements**: Description (text), duration (minutes), timestamp, optional note
- **Daily Notes**: Text content, timestamp, date (no measurement value)

### Data Relationships
- User → Measurements (One-to-Many)
- Measurement Types → Measurements (One-to-Many)
- All measurement types support optional notes field

## Integration Guidelines

### Livewire Integration
- **Traditional Components**: Use for complex, reusable UI components
- **Volt Components**: Use for simple forms and page-level components  
- **Flux State Management**: Centralized state for measurement data, date selection, and UI state
- **Reactive Patterns**: Components automatically update when Flux state changes
- **Repository Integration**: Inject repositories into components for data access
- **Validation**: Use Laravel validation within both traditional and Volt components

### Authentication Integration
- Use Laravel's built-in authentication system
- Simple email/password registration and login
- Session-based authentication (no API tokens needed)
- Laravel middleware for route protection

### Testing Integration
- **Pest**: Unit tests for Models, Services, and business logic
- **Pest**: Feature tests for HTTP endpoints and Livewire components
- **Laravel Dusk**: Browser testing for complete user workflows
- **Playwright**: Additional e2e testing capabilities
- **Docker**: Consistent testing environment matching production

### Testing Philosophy
- **No Mocking Business Logic**: Test real implementations with real database interactions
- **Public Interface Testing**: Test services/controllers that use repositories, not repositories directly
- **Database State Management**: Use `DatabaseTransactions` or `RefreshDatabase` for test isolation
- **Integration Over Unit**: Prefer integration tests that verify real system behavior
- **Repository Testing**: Skip direct repository testing (CRUD operations) or use real database
- **Service Testing**: Focus on testing business logic in service classes with real dependencies

### Playwright Testing Standards
- **Mandatory Assertions**: All Playwright tests MUST use `expect()` assertions to verify UI state and behavior
- **No Conditional Logic**: Tests MUST NOT use `if` statements to conditionally execute test steps
- **Fail-Fast Principle**: Tests MUST fail immediately when expected UI elements are not found
- **Explicit Element Selection**: Use specific selectors (`getByRole`, `getByText`, etc.) instead of generic CSS selectors
- **Step Verification**: Each test step MUST be verified with assertions before proceeding to the next step
- **No Silent Failures**: Tests that skip actions due to missing elements are considered invalid and must be rewritten

## Coding Standards

### Laravel Standards
- Follow PSR-12 coding standards
- Use Laravel naming conventions
- Implement proper exception handling
- Use Laravel's built-in validation
- Follow SOLID principles

### Code Organization
- **Single Responsibility**: Each class has one responsibility
- **Dependency Injection**: Use Laravel's service container
- **Interface Segregation**: Create focused interfaces
- **Documentation**: PHPDoc comments for all methods
- **Type Hinting**: Use strict typing where possible

### Database Standards
- Use descriptive table and column names
- Follow Laravel migration conventions
- Create proper database indexes
- Use database transactions for data integrity

---

*Architecture guidance is referenced during task implementation as defined in `./.claude/workflow.md`*