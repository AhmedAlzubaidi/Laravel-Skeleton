# Laravel Skeleton – Project Overview

This project is a reusable **Laravel skeleton** designed to serve as a solid foundation for building clean, modular, and scalable Laravel APIs. It applies modern design patterns, architecture principles, and a curated set of packages.

---

## 🧱 Architecture & Design Principles

- **Action Pattern** (via [Laravel Actions](https://github.com/lorisleiva/laravel-actions))
  - All business logic lives in **Action classes**.
  - Two categories:
    - **Endpoint Actions**
      - Used to serve public API requests.
      - Located in `app/Http/Endpoints`
      - Follow the naming convention: `CreateUserEndpoint`, `LoginUserEndpoint`
    - **Internal Actions**
      - Used for non-endpoint business logic (e.g., image processing, data updates).
      - Located in `app/Actions`
      - Follow the naming convention: `UpdateProfileImage`, `SyncRoles`

- **Routes**
  - API routes are defined in `routes/api.php`
  - Routes are RESTful, structured around resources (e.g., `/api/users`, `/api/orders`).
  - Each route delegates to a single Endpoint Action, often following the REST method-to-action naming convention.

## 🧩 Example: Users Resource
```php
use Illuminate\Support\Facades\Route;
use App\Http\Endpoints\Users\{
    GetUsersEndpoint,
    GetUserEndpoint,
    CreateUserEndpoint,
    UpdateUserEndpoint,
    DeleteUserEndpoint
};

Route::prefix('users')->group(function () {
    Route::get('/', GetUsersEndpoint::class);          // index
    Route::get('/{id}', GetUserEndpoint::class);       // show
    Route::post('/', CreateUserEndpoint::class);       // store
    Route::put('/{id}', UpdateUserEndpoint::class);    // update
    Route::delete('/{id}', DeleteUserEndpoint::class); // destroy
});
```

- **Service Layer**
  - Located in `app/Services`
  - For interacting with external services, APIs, or microservices.

- **Form Requests & Custom Validation Rules**
  - Located in `app/Http/Requests` and `app/Rules`
  - Standardized validation flow using Laravel’s `FormRequest`.

- **Enum Usage**
  - Located in `app/Enums`
  - For type-safe representation of status, roles, types, etc.

---

## 📦 Core Packages

- [`lorisleiva/laravel-actions`](https://github.com/lorisleiva/laravel-actions) – Structuring business logic into reusable actions.
- [`filamentphp/filament`](https://github.com/laravel/passport) – Admin panel and form builder powered by Livewire (great for internal tools and dashboards).
- [`laravel/passport`](https://github.com/laravel/passport) – OAuth2 server implementation for API authentication.
- [`spatie/laravel-data`](https://github.com/spatie/laravel-data) – Typed DTOs & transformers.
- [`spatie/laravel-health`](https://github.com/spatie/laravel-health) – Health and system checks for your application.
- [`laravel/pint`](https://github.com/laravel/pint) – Opinionated code style formatting.
- [`barryvdh/laravel-ide-helper`](https://github.com/barryvdh/laravel-ide-helper) – IDE autocompletion for models, facades etc.
- [`wulfheart/laravel-actions-ide-helper`](https://github.com/Wulfheart/laravel-actions-ide-helper) – IDE autocompletion for actions.

---

## ✅ Testing & Quality
- **Pest** – Robust Unit/Integration/E2E testing Framework.
- **Mockery** – Dependency mocking in unit tests.
- **Factories** – Used for seeding data for testing.
- **Laravel Pint** – Enforces consistent coding style.

---

## 📂 Project Structure

```bash
app/
├── Actions/                # Non-endpoint internal actions
│   └── Profile/
│       └── UpdateProfileImage.php
│
├── Http/
│   ├── Endpoints/          # Endpoint-serving actions
│   │   └── User/
│   │       └── CreateUserEndpoint.php
│   ├── Requests/           # FormRequest validation
│
├── Services/               # External API or microservice integrations
│   └── ImageService.php
│
├── Enums/                  # Enum classes
│   └── UserStatus.php
│
├── Rules/                  # Custom validation rules
│   └── ValidSaudiPhoneNumber.php
```

---

## 🚀 Goal
The primary goal is to enable rapid development of robust, modular, and testable Laravel applications, with clear separation of responsibilities and modern PHP practices.