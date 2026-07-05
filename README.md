# <img src="https://api.iconify.design/lucide:clipboard-list.svg?color=%238A2BE2" width="32" align="top" /> Team 4 — ThreeDOS Backend — Task Session 9

![Status](https://img.shields.io/badge/status-active-10b981?style=flat-square)
![Role](https://img.shields.io/badge/role-Backend_Team-8A2BE2?style=flat-square)
![Stack](https://img.shields.io/badge/stack-Laravel_13-FF2D20?style=flat-square)
![Session](https://img.shields.io/badge/session-Task_9-4F5B93?style=flat-square)

<br />

RESTful API for [**project name — TBD**] built by **Team 4 Backend**. Delivers authenticated endpoints with JWT, role-based authorization (Admin/User), paginated collections, Redis caching, validated requests, and interactive API docs via Scramble. Frontend team consumes JSON responses at `/api/*` — full endpoint reference available at `/docs/api` after setup.

---


## <img src="https://api.iconify.design/lucide:list-checks.svg?color=%238A2BE2" width="24" align="top" /> Backend Requirements

### 1. Authentication Module

The system must provide a complete authentication module that includes:

- User registration (Sign Up)
- User login
- User logout
- JWT or token-based authentication
- Forgot Password functionality
- Password Reset using a secure reset token

### 2. Authorization

The application must implement authorization mechanisms to control access to resources. Requirements include:

- Role-Based Access Control (RBAC) or Permission-Based Authorization
- Different user roles (e.g., Admin, User)
- Protected routes based on user permissions
- Policies or Gates for resource ownership validation

### 3. Pagination

Endpoints returning collections of data must support pagination. Requirements include:

- Configurable page size
- Current page information
- Total number of records
- Total pages
- Navigation metadata (Next Page, Previous Page)
- Support for query parameters such as:
  - `page`
  - `per_page`

Example response metadata:

```json
{
  "current_page": 1,
  "per_page": 10,
  "total": 150,
  "last_page": 15
}
```

### 4. Caching

The backend must utilize caching to improve application performance and reduce database load.

### 5. API Validation

All incoming requests must be validated before processing.

### 6. API Documentation

The project must provide interactive API documentation.

---

## <img src="https://api.iconify.design/lucide:layers.svg?color=%238A2BE2" width="24" align="top" /> Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.5.8 (ZTS) |
| Framework | Laravel 13 |
| Database | SQLite |
| Monitoring | Laravel Telescope 5 |
| API Documentation | Dedoc Scramble |
| Authentication | JWT (tymon/jwt-auth) |
| Caching | Redis (predis/predis) |
| Testing | PHPUnit 12 / Pest |

---

## <img src="https://api.iconify.design/lucide:folder-tree.svg?color=%238A2BE2" width="24" align="top" /> Project Structure

```
t4-3dos-backend-task9/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── TelescopeServiceProvider.php
│
├── bootstrap/
├── config/
│   ├── telescope.php
│   └── ...
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── public/
├── resources/
│   └── views/            # Email templates only
├── routes/
├── storage/
├── tests/
│
├── composer.json
└── README.md
```

---

## <img src="https://api.iconify.design/lucide:terminal.svg?color=%238A2BE2" width="24" align="top" /> Quick Start

### Prerequisites

- PHP 8.5.8 (ZTS — Thread-Safe)
- Composer 2.x
- Redis Server 7+ (required for caching)
- SQLite (preview with [VS Code SQLite Viewer](https://marketplace.visualstudio.com/items?itemName=qwtel.sqlite-viewer))

### Installation

```bash
git clone https://github.com/AhmedTyson/t4-3dos-backend-task9.git
cd t4-3dos-backend-task9

# Full setup (recommended)
composer run setup
```

Or step by step:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
```

### Available Commands

| Command | Description |
|---------|-------------|
| `composer run setup` | Full project setup (install, env, key, jwt, migrate) |
| `composer run dev` | Start Laravel dev server |
| `composer run test` | Clear config & run tests |

### Redis Caching

Update `.env`:

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## <img src="https://api.iconify.design/lucide:route.svg?color=%238A2BE2" width="24" align="top" /> Development Roadmap

| Phase | Focus | Description |
|-------|-------|-------------|
| 1 | Authentication | Registration, Login, Logout, JWT |
| 2 | Password Management | Forgot Password, Reset Password |
| 3 | RBAC | Roles, Permissions, Gates, Policies |
| 4 | Core Modules | CRUD modules (min 4) |
| 5 | Pagination | Paginated endpoints with metadata |
| 6 | Caching | Cache queries, responses, routes |
| 7 | Validation | Form Requests, validation rules |
| 8 | API Documentation | Scramble annotations |
| 9 | Testing | Feature & Unit tests |
| 10 | Polish & Deploy | Review, optimize, deploy |

---

## <img src="https://api.iconify.design/lucide:users.svg?color=%238A2BE2" width="24" align="top" /> Team 4

| Name | Role |
|------|------|
| Ahmed Elsayed (Ahmed Tyson) | Team Leader |
| Mohamed Osama | Co-Leader |
| Remas Ayman | Team Member |
| Sarah Mahmoud | Team Member |
| Fady Osama | Team Member |
| Youssef Hany | Team Member |
| Rana Medhat | Team Member |
| Sama Refaat | Team Member |

---

## <img src="https://api.iconify.design/lucide:building-2.svg?color=%238A2BE2" width="24" align="top" /> About ThreeDOS

ThreeDOS is a student-run initiative that replicates the structure and expectations of a professional software company. Members are assigned to functional departments (backend, frontend, design, product) and deliver real work under real constraints. The goal is to close the gap between academic learning and industry readiness.

> Organization: [github.com/Threedos](https://github.com/Threedos)

---

## <img src="https://api.iconify.design/lucide:user.svg?color=%238A2BE2" width="24" align="top" /> Author

**Ahmed Elsayed**
Team Leader — Backend Development @ ThreeDOS
[github.com/AhmedTyson](https://github.com/AhmedTyson)
