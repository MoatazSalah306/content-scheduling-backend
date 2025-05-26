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
- Rate limiting: Max 10 scheduled posts per day
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
git clone <repo-url>
cp .env.example .env
composer install
php artisan migrate --seed
php artisan serve
