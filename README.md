Content Scheduler Backend (PHP/Laravel)
Overview
This repository contains the backend implementation of the Content Scheduler application, developed as part of the GetPayIn Backend Developer Coding Challenge. Built with PHP 8.1 and Laravel 11, the application enables users to create, schedule, and manage posts across multiple social media platforms. The codebase prioritizes clean architecture, performance optimization, and robust feature implementation, adhering to SOLID principles and modern development practices.
Features

Authentication: Secure user registration, login, and logout using Laravel Sanctum with token-based authentication.
Post Management:
Create, read, update, and delete posts with title, content, optional image URL, scheduled time, and platform selection.
Filter posts by status (draft, scheduled, published) and date.
Validation for platform-specific requirements (e.g., character limits for Twitter, Instagram, LinkedIn).


Platform Management:
List all available platforms (Twitter, Instagram, LinkedIn, etc.).
Toggle active platforms for users and retrieve enabled platforms.


Scheduling:
A Laravel command (php artisan publish:posts) and queued job to process due posts.
Mocked publishing process to simulate posting to social platforms.


Creative Features:
Post Analytics: Provides detailed analytics via /analytics, /analytics/quick-stats, and /analytics/platform/{platformId} endpoints, including posts per platform, publishing success rate, and scheduled vs. published counts.
Rate Limiting: Restricts users to 10 scheduled posts per day using Laravel's throttle middleware.
Activity Logging: Logs user actions (e.g., post creation, updates) for auditability using a custom logging system.
Custom Feature: Scheduled post preview generation for each platform to ensure content compliance before publishing.



Tech Stack

Framework: Laravel 11
Language: PHP 8.1
Database: MySQL (with migrations and seeders)
Authentication: Laravel Sanctum
Queue: Laravel Queue with Redis for job processing
Testing: PHPUnit for unit and feature tests
Caching: Laravel Cache (Redis driver) for optimized API responses

Installation

Clone the Repository:
git clone https://github.com/yourusername/content-scheduler-backend.git
cd content-scheduler-backend


Install Dependencies:
composer install


Configure Environment:

Copy .env.example to .env and update the following:
Database credentials (DB_*)
Redis configuration (REDIS_*)
App URL (APP_URL)




Run Migrations and Seeders:
php artisan migrate --seed


Set Up Queue Worker:
php artisan queue:work --queue=default


Start the Development Server:
php artisan serve


Run Scheduled Tasks (for post publishing):
php artisan publish:posts



API Endpoints
Authentication

POST /api/register - Register a new user
POST /api/login - Authenticate a user and return a token
POST /api/logout - Log out the authenticated user
GET /api/profile - Get authenticated user profile
PUT /api/profile - Update user profile

Posts

GET /api/posts - List user's posts (with filters: status, date)
POST /api/posts - Create a new post
GET /api/posts/{id} - Get a specific post
PUT /api/posts/{id} - Update a scheduled post
DELETE /api/posts/{id} - Delete a post

Platforms

GET /api/platforms - List available platforms
POST /api/platforms/toggle - Toggle active platforms for the user
GET /api/platforms/getActive - Get enabled platforms for the user

Analytics

GET /api/analytics - Get detailed analytics for posts
GET /api/analytics/quick-stats - Get quick statistics (e.g., total posts, success rate)
GET /api/analytics/platform/{platformId} - Get analytics for a specific platform

Performance Optimizations

Eloquent Query Optimization: Efficient use of eager loading (with) to minimize N+1 queries.
Caching: API responses cached using Redis for frequently accessed data (e.g., platform lists, analytics).
Queueing: Post publishing handled asynchronously via Laravel queues with Redis to ensure scalability.
Database Indexing: Indexes on frequently queried columns (scheduled_time, status, user_id) for faster retrieval.

Code Quality

SOLID Principles: Modular design with service classes, repositories, and DTOs for clear separation of concerns.
Type Safety: Use of PHP type hints and strict typing.
Documentation: PHPDoc comments for all major classes and methods.
Testing: Comprehensive PHPUnit tests covering models, controllers, and jobs.
Validation: Robust request validation with custom rules for platform-specific constraints.

Security

Authentication: Sanctum ensures secure token-based authentication.
Rate Limiting: Throttle middleware limits excessive API calls (e.g., 10 posts/day).
Input Validation: Strict validation to prevent injection attacks.
CSRF Protection: Laravel's built-in CSRF protection for form submissions.

Trade-Offs

Mocked Publishing: A mocked publishing process was implemented to focus on scheduling logic, as per the challenge requirements.
Caching Strategy: Redis was chosen for performance, though file-based caching could be an alternative for simpler deployments.
Activity Logging: Stored in the database for persistence, which may impact performance under high load; a future improvement could involve offloading logs to a dedicated logging service.

Video Demo
A video walkthrough of the project is available here. It demonstrates:

User registration, login, and logout
Post creation, scheduling, and updating
Platform toggling and retrieval of active platforms
Analytics dashboard and scheduled post processing via php artisan publish:posts

Future Improvements

Integration with real social media APIs for actual publishing.
Advanced analytics with visual charts using a library like Chart.js.
Support for bulk post scheduling and recurring posts.

Contact
For any questions, please reach out to Mohamed Salah at msalahbussines2005@gmail.com.
Thank you for reviewing my submission!
