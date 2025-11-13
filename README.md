# Laravel API Code Test

## Overview

This project implements a simple RESTful API using **Laravel**, focusing on **clean code**, **maintainability**, and **clarity of structure** rather than complex logic correctness.  
It is part of a coding test to demonstrate proper naming, code organization, and readability.

---

## Features

- `POST /api/v1/users` — Create a new user.
- `GET /api/v1/users` — List all active users with pagination, search, and sorting.
- Automatically sends:
  - Welcome email to the new user.
  - Notification email to the admin.
- Includes computed fields:
  - `orders_count` — total number of orders per user.
  - `can_edit` — whether the authenticated user can edit that user record (based on role rules).

---

## Tech Stack

- Laravel (latest stable version)
- PHP 8.2+
- Eloquent ORM
- Laravel Mail (Mailables)
- SQLite/MySQL (configurable)
- PHPUnit for feature testing

---

## Requirements

- PHP >= 8.2
- Composer
- Laravel CLI (`artisan`)
- SQLite or MySQL for database
- (Optional) Mailtrap or Log driver for mail testing

---

## Installation

```bash
git clone https://github.com/your-username/api-test.git
cd api-test
composer install
cp .env.example .env
php artisan key:generate
```

### Configure the environment
In `.env` file:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

MAIL_MAILER=log
ADMIN_EMAIL=admin@example.com
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="API Test"
```

Then run:
```bash
touch database/database.sqlite
php artisan migrate
```

---

## Usage

Start the application:
```bash
php artisan serve
```

### Create User
**POST** `/api/v1/users`

Example request body:
```json
{
  "email": "alice@example.com",
  "password": "password123",
  "name": "Alice",
  "role": "user"
}
```

Response (201):
```json
{
  "id": 1,
  "email": "alice@example.com",
  "name": "Alice",
  "role": "user",
  "created_at": "2025-11-13T08:00:00Z"
}
```

### List Users
**GET** `/api/v1/users`

Query parameters:
- `search` — filter by name or email.
- `page` — pagination (default 1).
- `sortBy` — one of: `name`, `email`, `created_at`.

Example:
```
GET /api/v1/users?search=alice&sortBy=name&page=1
```

Response (200):
```json
{
  "page": 1,
  "per_page": 15,
  "total": 3,
  "users": [
    {
      "id": 1,
      "email": "alice@example.com",
      "name": "Alice",
      "role": "user",
      "created_at": "2025-11-13T08:00:00Z",
      "orders_count": 2,
      "can_edit": true
    }
  ]
}
```

---

## Role Rules for `can_edit`

| Authenticated Role | Editable User | Condition |
|--------------------|---------------|------------|
| Administrator | All users | Always true |
| Manager | Users only | target.role === 'user' |
| User | Self only | target.id === actor.id |

---

## Project Structure (Laravel default)

```
app/
 ├── Http/
 │   ├── Controllers/Api/UserController.php
 │   ├── Requests/{StoreUserRequest, ListUserRequest}.php
 │   └── Resources/UserResource.php
 ├── Mail/{WelcomeUser, NotifyAdmin}.php
 ├── Models/{User, Order}.php
routes/
 └── api.php
database/
 └── migrations/
tests/
 └── Feature/
     ├── CreateUserTest.php
     └── ListUsersTest.php
```

---

## Testing

Run all tests:
```bash
php artisan test
```

### Example tests included:
- `CreateUserTest` — verifies POST /api/v1/users creates a user, sends both emails, and returns correct JSON.
- `ListUsersTest` — verifies GET /api/v1/users includes pagination, orders_count, and can_edit field logic.

---

## Code Style

- Follows **PSR-12** and **Laravel naming conventions**.
- Controller methods are concise and descriptive.
- Validation handled via **FormRequest**.
- Responses standardized using **API Resource**.
- Mailables encapsulate email templates.
- Logic is separated and easily testable (e.g. `computeCanEdit()` helper method).

---

## Author

**Prastiyo Beka**  
_[GitHub](https://github.com/your-username) · [LinkedIn](https://www.linkedin.com/in/prastiyo-beka-22b5085a/)_  

---

## Notes for Reviewers

> The main purpose of this project is to demonstrate how I structure and organize Laravel code — focusing on clarity, maintainability, and developer readability — rather than complex business logic or full authentication layers.
>
> All code was written manually following Laravel best practices.

---
