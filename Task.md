# Test Cases & Assessment Framework (Take-Home)

## 1. Assessment dan Evaluasi Kriteria

### Assessment Focus
- Code quality and organization
- Go best practices implementation
- Concurrent programming skills
- Fulfill business logical requirements
- Database design and optimization
- API design principles
- Error handling and resilience
- Testing approach and coverage
- Documentation quality
- Problem-solving creativity
- Security considerations

### Evaluation Criteria
- Go Functionality (30%): Feature completeness and correctness
- Go Code Quality (25%): Structure, readability, and maintainability
- PHP (20%): Business requirement fulfillment
- Performance (10%): Efficient resource usage and scalability
- Testing (10%): Test coverage and quality
- Documentation (5%): Clear setup and architecture explanation

### General Deliverables
- Complete source code with Git history
- Database schema and migration files
- API documentation (Swagger/OpenAPI preferred)
- Docker configuration
- Comprehensive README with:
  - Setup and running instructions
  - Architecture explanation
  - Technology choices justification
  - Known limitations and future improvements
  - Test coverage report
- Live demo (optional - deployed version)

---

## 2. Golang

### Test Case 1: Concurrent Data Processing
**Scenario**: Build a concurrent file processor that:
- Reads multiple CSV files simultaneously
- Processes data with worker pool pattern
- Handles errors gracefully
- Provides progress tracking

**Expected Skills Tested**:
- Goroutines and channels
- Error handling
- Memory management
- Code organization

### Test Case 2: REST API Development
**Scenario**: Create a RESTful API for a simple blog system:
- User authentication and authorization
- CRUD operations for posts and comments
- Input validation and error responses
- Database integration with transactions

**Expected Skills Tested**:
- HTTP handlers and middleware
- Database operations
- JSON handling
- Security considerations

### AI Tools Usage
**Scenario**: Candidates are encouraged to use AI tools such as Cursor, Claude, or Gemini CLI during the Go Programming Test.

**Expected Behavior**:
- Candidate can leverage AI to scaffold boilerplate or speed up repetitive tasks
- Shows discernment in verifying AI-generated code
- Maintains ownership of logic, architecture, and quality

**Assessment Focus**:
- Efficiency and productivity gains using AI tools
- Critical thinking in reviewing AI suggestions
- Proper integration of AI-assisted and custom-written logic

---

## 3. Laravel

**Scenario**: Using Laravel or CodeIgniter, implement the following requirements based on the provided SQL schema:
- **Authentication**: Create a login function using JWT for authorization.
- **Reporting Engine**:
  - Generate a monthly revenue report for Merchants (November) and Outlets (August).
  - **Constraint**: Reports must include pagination and display "0" revenue for dates with no transactions.
- **Data Security**: Implement strict Multi-Tenancy; users must only access data belonging to their specific `merchant_id` or `outlet_id`.
- **Optimization**: Provide DML documentation and justify table indexing strategies to improve report performance.

### Coding Guidelines
a. For PHP, use the CodeIgniter or Laravel framework.
b. Apply REST API best practices when determining Response Codes.

### Notes
- Revenue (Omzet) is defined as the total sum of bill amounts (`bill_total`).
- SQL schema provided below.

### SQL Schema
```sql
CREATE TABLE `Merchants` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(40) NOT NULL,
  `merchant_name` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

insert into Merchants values 
(1, 1, 'merchant 1', now(), 1, now(),1), 
(2, 2, 'Merchant 2', now(), 2, now(),2);

CREATE TABLE `Outlets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `outlet_name` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

insert into Outlets values 
(1, 1, 'Outlet 1', now(), 1, now(),1), 
(2, 2, 'Outlet 1', now(), 2, now(),2), 
(3, 1, 'Outlet 2', now(), 1, now(),1);

CREATE TABLE `Transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `outlet_id` bigint(20) NOT NULL,
  `bill_total` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

insert into Transactions values
(1, 1, 1, 2000, '2026-08-01 12:30:04', 1, '2026-08-01 12:30:04',1),
(2, 1, 1, 2500, '2026-08-01 17:20:14', 1, '2026-08-01 17:20:14',1),
(3, 1, 1, 4000, '2026-08-02 12:30:04', 1, '2026-08-02 12:30:04',1),
(4, 1, 1, 1000, '2026-08-04 12:30:04', 1, '2026-08-04 12:30:04',1),
(5, 1, 1, 7000, '2026-08-05 16:59:30', 1, '2026-08-05 16:59:30',1),
(6, 1, 3, 2000, '2026-08-02 18:30:04', 1, '2026-08-02 18:30:04',1),
(7, 1, 3, 2500, '2026-08-03 17:20:14', 1, '2026-08-03 17:20:14',1),
(8, 1, 3, 4000, '2026-08-04 12:30:04', 1, '2026-08-04 12:30:04',1),
(9, 1, 3, 1000, '2026-08-04 12:31:04', 1, '2026-08-04 12:31:04',1),
(10, 1, 3, 7000, '2026-08-05 16:59:30', 1, '2026-08-05 16:59:30',1),
(11, 2, 2, 2000, '2026-08-01 18:30:04', 2, '2026-08-01 18:30:04',2),
(12, 2, 2, 2500, '2026-08-02 17:20:14', 2, '2026-08-02 17:20:14',2),
(13, 2, 2, 4000, '2026-08-03 12:30:04', 2, '2026-08-03 12:30:04',2),
(14, 2, 2, 1000, '2026-08-04 12:31:04', 2, '2026-08-04 12:31:04',2),
(15, 2, 2, 7000, '2026-08-05 16:59:30', 2, '2026-08-05 16:59:30',2),
(16, 2, 2, 2000, '2026-08-05 18:30:04', 2, '2026-08-05 18:30:04',2),
(17, 2, 2, 2500, '2026-08-06 17:20:14', 2, '2026-08-06 17:20:14',2),
(18, 2, 2, 4000, '2026-08-07 12:30:04', 2, '2026-08-07 12:30:04',2),
(19, 2, 2, 1000, '2026-08-08 12:31:04', 2, '2026-08-08 12:31:04',2),
(20, 2, 2, 7000, '2026-08-09 16:59:30', 2, '2026-08-09 16:59:30',2),
(21, 2, 2, 1000, '2026-08-10 12:31:04', 2, '2026-08-10 12:31:04',2),
(22, 2, 2, 7000, '2026-08-11 16:59:30', 2, '2026-08-11 16:59:30',2);
```

---

## 4. Database

### Test Case: Database Schema Design
**Scenario**: Design database schema for a social media platform

**Requirements**:
- User profiles and relationships (followers/following)
- Posts with multimedia content
- Comments and reactions
- Private messaging system
- Activity feeds and notifications

**Tasks**:
- Create normalized database schema
- Define relationships and constraints
- Design indexes for common queries
- Write complex SQL queries for feed generation
- Propose caching strategy

**Assessment Criteria**:
- Normalization and data integrity
- Performance optimization
- Scalability considerations
- Query efficiency
