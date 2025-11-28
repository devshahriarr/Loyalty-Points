## Loyalty Point Management – Backend

A multi-tenant loyalty point management backend built on **Laravel 11**, designed to support:

- **Landlord (system)** side for managing business owners and tenants.
- **Tenant** side per business, with isolated databases using **Spatie Multitenancy**.
- **JWT-based APIs** for authentication and business/loyalty data access.

This repository currently contains the **backend** codebase (`backend/`); any separate frontend (if added later) should be documented here as well.

---

## Project Overview

- **Domain**: Loyalty & rewards management (businesses, branches, customers, cards, points, offers, visits, subscriptions).
- **Architecture**: Laravel monolith with **landlord + tenant** database pattern.
- **Tenancy**:
  - Landlord DB holds `users`, `businesses`, `tenants`, roles/permissions.
  - Each tenant DB holds customers, cards, points, branches, offers, etc.
  - Tenant DBs are created and migrated automatically when a business is approved.
- **Authentication**:
  - **JWT** (`tymon/jwt-auth`) via `auth:api` guard.
  - **Spatie Permission** for roles (e.g. `system_admin`, `business_owner`).

---

## Tech Stack

- **Language**: PHP 8.2+
- **Framework**: Laravel 11
- **Auth**: `tymon/jwt-auth`
- **Multitenancy**: `spatie/laravel-multitenancy`
- **Authorization / RBAC**: `spatie/laravel-permission`
- **Database**: MySQL (landlord + tenant connections)
- **Queue / Jobs**: Database queue
- **Frontend assets**: Vite, Tailwind (default Laravel welcome page)

---

## Installation & Setup

### 1. Prerequisites

- PHP 8.2+
- Composer
- MySQL 8+ (or compatible)
- Node.js 18+ & npm (for Vite/dev assets)

### 2. Clone & Install

```bash
git clone <your-repo-url>
cd backend

composer install
npm install
```

### 3. Environment Configuration

Copy `.env.example` to `.env` (or update `env.txt` values into `.env`) and configure:

- **Application**
  - `APP_NAME=Loyalty`
  - `APP_ENV=local`
  - `APP_URL=http://localhost`
  - `APP_KEY` (run `php artisan key:generate` if missing)

- **Landlord DB**
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=loyalty`
  - `DB_USERNAME=...`
  - `DB_PASSWORD=...`

- **JWT**
  - `JWT_SECRET=` (run `php artisan jwt:secret` to generate; **do not commit this**)

> **Security Note**: Never commit real `.env` files or secrets (JWT, DB credentials) to version control.

### 4. Database & Seeds

Run landlord migrations and seeds:

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=SystemAdminSeeder
```

When a business owner is approved, tenant databases are created and migrated automatically via the `Tenant` model hook.

### 5. Run the Application

For local development (using the Composer script):

```bash
composer run dev
```

This will run:

- `php artisan serve`
- `php artisan queue:listen`
- `php artisan pail`
- `npm run dev` (Vite)

You can also run them individually if preferred.

---

## Directory Structure (Backend)

Key directories inside `backend/`:

- **`app/`**
  - **`Http/Controllers`**: API controllers (auth, admin approval, business CRUD, tenant test, etc.).
  - **`Http/Middleware`**: `EnsureTenantExists` for resolving tenant from domain.
  - **`Models`**:
    - `User`, `LandlordUser`, `Business`, `Tenant`
    - Tenant-aware models: `Customer`, `CustomerPoint`, `Branch`, `LoyaltyCard`, `Offer`, `VisitLog`, etc.
  - **`Providers`**:
    - `TenantRouteServiceProvider` – registers tenant-specific routes.
  - **`Console/Commands`**:
    - `DebugTenantCreation` – helper for debugging landlord/tenant role assignment.

- **`config/`**
  - `auth.php` – JWT-based `api` guard.
  - `multitenancy.php` – Spatie multitenancy configuration.
  - `permission.php` – Spatie permission configuration.

- **`database/`**
  - `migrations/` – landlord tables (users, businesses, tenants, permissions, etc.).
  - `migrations/tenants/` – tenant tables (branches, customers, loyalty_cards, points, etc.).
  - `seeders/` – base roles, system admin user.

- **`routes/`**
  - `web.php` – default Laravel welcome page.
  - `api.php` – landlord/system APIs (business registration, login, business CRUD, admin approval).
  - `tenant.php` – tenant-specific APIs mounted under `/api` with `tenant` middleware.

---

## Core Features (Current Implementation)

- **Landlord / System**
  - Business-owner registration request (`/api/business-register`).
  - System admin approves pending business owners and:
    - Assigns `business_owner` role.
    - Creates a `Business` record.
    - Creates a `Tenant` with its own database and runs tenant migrations.

- **Authentication**
  - `POST /api/login` – issue JWT for active users.
  - `GET /api/me` – returns authenticated user.
  - `POST /api/logout` – invalidates token.
  - `POST /api/refresh` – refreshes JWT.

- **Business Management**
  - `Route::apiResource('business', BusinessController::class)`:
    - Basic CRUD for `Business` (currently without validation or authorization scoping).

- **Tenancy**
  - `EnsureTenantExists` middleware resolves tenant based on host.
  - `Tenant` model:
    - Creates tenant DB if not exists.
    - Runs tenant migrations against `tenant` connection.
  - `TenantAwareModel` trait:
    - Automatically sets `tenant_id` on create.
    - Adds global `tenant` scope for isolation.

---

## API Endpoints Summary (High-Level)

### Landlord / System API (`routes/api.php`)

- **Public**
  - `POST /api/business-register` – request registration as business owner.
  - `POST /api/login` – email/password login (only active users).

- **Authenticated (`auth:api`)**
  - `GET /api/me` – current user details.
  - `POST /api/logout` – logout.
  - `POST /api/refresh` – refresh token.
  - `apiResource /api/business` – CRUD operations on `Business`.

- **Admin-only (`auth:api`, `role:system_admin`)**
  - `POST /api/admin/approve-business-owner/{id}` – approve pending landlord user and bootstrap business + tenant.

### Tenant API (`routes/tenant.php`)

Mounted with `tenant` + `api` middleware and `api` prefix, for example:

- **Public (per tenant)**
  - `POST /api/register` – tenant-side user registration.
  - `POST /api/login` – tenant-side login.
  - `GET /api/tenant-info` – basic tenant info/test.
  - `GET /api/tenant-test-db` – DB connectivity test.

- **Protected (planned, currently commented/empty controllers)**
  - Resource routes for:
    - `branches`
    - `customers`
    - `loyalty-cards`
    - `customer-points`
    - `visit-logs`
    - `offers`

> As the project evolves, expand this section with detailed request/response schemas and auth/role constraints.

---

## Developer Guide

- **Coding Guidelines**
  - Use **form requests** for validation instead of inline `Validator::make`.
  - Avoid `$request->all()` for mass assignment; explicitly whitelist with DTOs or validated data.
  - Use **service classes** for complex business logic (e.g. tenant creation, point calculation).
  - Keep tenant-aware code (models, queries) carefully separated from landlord logic.

- **Multitenancy Tips**
  - When writing models that live in tenant DBs, use `TenantAwareModel` to enforce `tenant_id` scoping.
  - Avoid heavy work in `Tenant::booted()`; long-running tasks should be queued.
  - Always consider which connection (`landlord` vs `tenant`) a query should use.

- **Testing**
  - Add tests under `tests/Feature` and `tests/Unit`.
  - Test both landlord and tenant flows (tenant resolution, DB isolation, permissions).

---

## Roadmap & Next Steps

- Implement full tenant-side resources (controllers, routes, validation) for branches, customers, cards, points, and offers.
- Harden security around mass assignment, authorization, and validation.
- Add API documentation (OpenAPI/Swagger) and Postman collection.
- Introduce caching for common read-heavy endpoints.
- Add monitoring/logging dashboards for tenant operations and migrations.

---

## License

This project is based on Laravel and is available under the **MIT License**. See `LICENSE` for details.

