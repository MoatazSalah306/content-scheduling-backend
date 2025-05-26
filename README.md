# Content Scheduler API – Backend

This is the Laravel backend implementation for a **Content Scheduler** system. It provides a RESTful API to support scheduling, publishing, and managing social media posts across multiple platforms.

---

## Features

- User authentication with **Laravel Sanctum**
- CRUD for Posts with platform associations
- Post scheduling and automated publishing
- Platform management and toggling
- Analytics endpoints (summary and platform-specific)
- Profile management (view/update)
- Rate limiting: Max 10 scheduled posts per day. ( via PostStoreRequest ).
- Rate limiting on platforms toggling (5 toggles per minute) .
- Command/job runner for scheduled publishing

---

## API Routes

| Method | Endpoint                          | Description |
|--------|-----------------------------------|-------------|
| POST   | `/api/login`                      | User login |
| POST   | `/api/register`                   | User registration |
| GET    | `/api/profile`                    | Get profile |
| PUT    | `/api/profile`                    | Update profile |
| GET    | `/api/posts`                      | List posts |
| POST   | `/api/posts`                      | Create post |
| GET    | `/api/posts/{id}`                 | View post |
| PUT    | `/api/posts/{id}`                 | Update post |
| DELETE | `/api/posts/{id}`                 | Delete post |
| GET    | `/api/platforms`                  | List platforms |
| POST   | `/api/platforms/toggle`           | Toggle platform status |
| GET    | `/api/platforms/getActive`        | Get active platforms |
| GET    | `/api/analytics`                  | Summary analytics |
| GET    | `/api/analytics/quick-stats`      | Quick statistics |
| GET    | `/api/analytics/platform/{id}`    | Platform analytics |
| POST   | `/api/logout`                     | Logout |

> All routes (except auth) are protected via `auth:sanctum`.

---

## Scheduled Publishing Command

A Laravel command handles publishing scheduled posts using queue workers.

```bash
php artisan publish:posts
```
---

## Installation & Setup

```bash
git clone https://github.com/MoatazSalah306/content-scheduling-backend.git
cp .env.example .env
composer install
php artisan migrate --seed
php artisan serve
```
This Command runs the Analytics seeder to be able to test the analytics :
```php artisan db:seed --class=AnalyticsSeeder ```
This Command runs the Platforms seeder only :
 ```php artisan migrate --seed ```



 
---
# Project Implementation Summary

## Approach and Trade-offs

### API Authentication with Laravel Sanctum
- Implemented lightweight token-based authentication ideal for an API-only backend.
- Supports multiple tokens per user for various clients (mobile, web).
- **Trade-offs:**
  - Simpler setup than JWT (Laravel Passport) but lacks advanced OAuth2 features.
  - Added token expiration and revocation logic for enhanced security.

### Caching for Post Loading
- Utilized Laravel’s cache system (Redis/file) to optimize database queries and improve response times.
- Implemented cache invalidation after post creation or update to maintain data consistency.
- **Trade-offs:**
  - Balances improved performance with overhead from cache invalidation.
  - Chose a simple caching strategy appropriate for project scope over complex solutions.

### Job and Command for Post Publishing
- Used Laravel command scheduled tasks to dispatch synchronous jobs (`dispatchSync`) for immediate post publishing.
- Simplifies queue management by avoiding separate queue workers.
- **Trade-offs:**
  - Reliable for smaller-scale apps.
  - Less scalable than asynchronous queues with dedicated workers for high-volume systems.

### Standardized API Responses with ApiResponse Trait
- Created a reusable trait to enforce consistent JSON response structures across the API.
- Improves maintainability and API client compatibility.
- **Trade-offs:**
  - Promotes consistency at the expense of some flexibility for custom responses.
  - Trait kept simple to avoid complexity.

### Request Validation with Form Requests
- Used Laravel Form Requests to encapsulate validation rules, custom error messages, and input sanitization.
- Enhances modularity, testability, and adheres to SOLID principles (SRP and OCP).
- **Trade-offs:**
  - Slightly increases number of classes and initial development time.
  - Facilitates easy extension for new validation needs in the future.

---

## Additional Notes
- Prioritized clean, readable code following SOLID principles with clear naming and documentation.
- Included basic platform-specific validations (e.g., Twitter’s character limit) with room for future extensibility.
- Designed the solution for scalability using Laravel’s built-in features like queues and caching.

---

*This approach balances simplicity, performance, maintainability, and scalability for the project’s scope.*

