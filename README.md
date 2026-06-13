# Laravel Skeleton – Project Overview

This project is a reusable **Laravel Skeleton**, crafted as a personal starting point for building Laravel APIs.

While originally built for personal use, this project is **open source and community-friendly** — contributions, ideas, and improvements are welcome to help evolve it into a more flexible and powerful foundation for Laravel API development.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+
- Composer

### Testing Requirements
For running tests, you'll need these PHP extensions:
- **ext-pdo_sqlite**: For in-memory database testing
- **ext-xdebug** or **ext-pcov**: For code coverage reports (only needed when running coverage commands)

### Project Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate Passport encryption keys
php artisan passport:keys

# Run database migrations
php artisan migrate

# Create admin user and roles
php artisan db:seed
```

### Development Commands
```bash
# Ide helper commands
php artisan ide-helper:generate
php artisan ide-helper:models --write-mixin

# Run tests
composer test

# Generate test coverage report
composer test:coverage

# Format code
composer lint

# Refactor code
composer refactor

# Start development server
php artisan serve
# To Access Filament admin panel
# visit: http://localhost:8000/admin
```

---

<details>
<summary><strong>🧱 Architecture & Design Principles</strong></summary>

### **Controller + Commands/Queries Pattern**
- **Controllers**: Thin coordination layer for HTTP requests
- **Commands**: DTOs for write operations (create, update) using Spatie Laravel Data
- **Queries**: DTOs for read operations (list, show) using Spatie Laravel Data
- **DTOs**: Data Transfer Objects for responses using Spatie Laravel Data
- **BaseData**: Common foundation class providing consistent behavior across all DTOs

### **Authorization & Permissions**
- **UserPolicy**: Enforces access control based on user roles
- **Spatie Permission**: Role-based access control
- **Admin Bypass**: Admins bypass all authorization checks via `Gate::before` callback

### **Routes**
- API routes are defined in `routes/api.php` with v1 versioning
- Routes are RESTful, structured around resources (e.g., `/api/v1/users`)
- Each route delegates to controller methods, which use Commands/Queries for validation

## 🧩 Example: Users Resource
```php
use App\Http\Controllers\UserController;

Route::middleware('auth:api')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return UserDto::from($request->user());
    });
    Route::apiResource('users', UserController::class);
});
```

## 🧩 Example: UserController with Commands/Queries
```php
final readonly class UserController
{
    public function index(GetUsersQuery $query): JsonResponse
    {
        Gate::authorize('viewAny', User::class);
        
        $users = User::query()
            ->when($query->username, fn($q, $username) => $q->where('username', 'like', "%{$username}%"))
            ->when($query->email, fn($q, $email) => $q->where('email', 'like', "%{$email}%"))
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return response()->json([
            ...UserDto::collect($users)->toArray(),
            'message' => __('Users fetched successfully'),
        ]);
    }

    public function store(CreateUserCommand $command): JsonResponse
    {
        Gate::authorize('create', User::class);
        $user = User::create($command->validated());

        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User created successfully'),
        ], 201);
    }
}
```

### **BaseData Foundation**
All Commands, Queries, and DTOs extend from `BaseData`, which provides:
- **FormRequest-like validated() method**: Filters out optional fields that are not provided in the request
- **Differences**: One difference is that validated returns default values if they are not null
- **Type safety**: Ensures consistent data handling across the application

## 🧩 Example: BaseData Implementation
```php
abstract class BaseData extends Data
{
    public static function rules(): array
    {
        return [];
    }

    /**
     * Get the instance as an array.
     * Behave similar to laravel FormRequest validated() method.
     * It filters out attributes that are not required and have no value.
     */
    public function validated(): array
    {
        $rules = static::rules();
        $data = parent::toArray();

        foreach ($data as $key => $value) {
            if ($this->attributeShouldBeRemoved($rules, $key, $value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
```

### **DTOs & Validation**
- **Commands**: Handle request validation for write operations using Spatie Laravel Data
- **Queries**: Handle request validation for read operations using Spatie Laravel Data  
- **DTOs**: Handle response serialization using Spatie Laravel Data
- All use Spatie Laravel Data for type safety and validation

## 🧩 Example: DTOs and Commands
```php
// CreateUserCommand - Request validation
final class CreateUserCommand extends BaseData
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
    ) {}

    public static function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:40', 'unique:users,username'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // Checks against data leaks via HaveIBeenPwned
            ],
        ];
    }
}

// GetUsersQuery - Request validation with pagination
final class GetUsersQuery extends BaseData
{
    public function __construct(
        public ?string $username,
        public ?string $email,
        #[MapInputName('per_page')] // Maps 'per_page' input to 'perPage' property
        public int $perPage = 10,
        public int $page = 1,
    ) {}

    public static function rules(): array
    {
        return [
            'username' => ['sometimes', 'required', 'string', 'max:40'],
            'email'    => ['sometimes', 'required', 'email', 'exists:users,email'],
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page'     => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}

// UserDto - Response serialization
final class UserDto extends BaseData
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public ?string $password,
    ) {}
}
```

</details>

<details>
<summary><strong>📦 Core Packages</strong></summary>

### **Core Framework & Authentication**
- [`laravel/framework`](https://laravel.com/) – The Laravel framework (v12.0)
- [`laravel/passport`](https://github.com/laravel/passport) – OAuth2 server for API authentication
- [`spatie/laravel-permission`](https://github.com/spatie/laravel-permission) – Role and permission management

### **Admin Panel**
- [`filament/filament`](https://filamentphp.com/) – Beautiful admin panel and application framework

### **Data & Validation**
- [`spatie/laravel-data`](https://github.com/spatie/laravel-data) – Typed DTOs & transformers
- [`spatie/laravel-typescript-transformer`](https://github.com/spatie/laravel-typescript-transformer) – Generate TypeScript types from DTOs

### **Development & IDE Support**
- [`barryvdh/laravel-ide-helper`](https://github.com/barryvdh/laravel-ide-helper) – IDE autocompletion for models, facades etc.
- [`laravel/pint`](https://github.com/laravel/pint) – Opinionated code style formatting
- [`rector/rector`](https://github.com/rectorphp/rector) – Automated code refactoring
- [`laravel/boost`](https://github.com/laravel/boost) – Laravel-focused MCP server for augmenting your AI powered local development experience.

### **Testing & Quality**
- [`pestphp/pest`](https://pestphp.com/) – Modern testing framework
- [`larastan/larastan`](https://github.com/larastan/larastan) – Static analysis for Laravel
- [`pestphp/pest-plugin-type-coverage`](https://github.com/pestphp/pest-plugin-type-coverage) – Type coverage analysis

### **Monitoring & Health**
- [`spatie/laravel-health`](https://github.com/spatie/laravel-health) – Health and system checks

### **Frontend Tools**
Although this project is primarily intended to serve as an API, I've included Prettier just in case — it doesn't hurt to have clean code. 🙂
- [`vite`](https://vitejs.dev/) – Frontend build tool
- [`tailwindcss`](https://tailwindcss.com/) – Utility-first CSS framework
- [`prettier`](https://prettier.io/) – Code formatter

</details>

<details>
<summary><strong>✅ Testing & Quality</strong></summary>

### **Testing Framework**
- **Pest** – Modern testing framework with expressive syntax
- **In-Memory Database** – SQLite `:memory:` for fast, isolated tests
- **Seeders** – Database seeding for consistent test data
- **Factories** – Model factories for test data generation

### **Architecture Tests**
- **PHP Architecture Tests** – Ensures code follows architectural principles
- **Strict Types** – Enforces strict typing throughout the application
- **Documentation** – Requires proper PHPDoc annotations
- **Code Quality** – Prevents use of debugging functions in production code

### **Test Structure**
```php
describe('User Controller - Admin Users', function () {
    beforeEach(function () {
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('name', 'admin')->first();
        $this->user = User::factory()->create(['name' => 'Normal User']);
        $this->user->assignRole('user');
        $this->actingAs($this->admin, 'api');
    });
    
    // Admin tests...
});

describe('User Controller - Normal Users', function () {
    beforeEach(function () {
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('name', 'admin')->first();
        $this->user = User::factory()->create(['name' => 'Normal User']);
        $this->user->assignRole('user');
        $this->actingAs($this->user, 'api');
    });
    
    // Normal user tests...
});
```

### **Test Coverage**
- **129 tests** covering all CRUD operations and architecture principles
- **386 assertions** ensuring comprehensive coverage
- **100% type coverage** across all classes
- **Authorization testing** for both admin and normal users
- **Validation testing** for all input fields including password strength and HaveIBeenPwned data leak checks
- **Error handling** (404, 403, 422 status codes)
- **Architecture compliance** testing
- **Unit tests** for UserPolicy and the BaseData foundation
- **Coverage reports** available via `composer test:coverage` command

</details>

<details>
<summary><strong>📂 Project Structure</strong></summary>

```bash
app/
├── Commands/...................... # Request DTOs for write operations
│   ├── CreateUserCommand.php
│   └── UpdateUserCommand.php
│
├── Queries/....................... # Request DTOs for read operations
│   └── GetUsersQuery.php
│
├── DTOs/.......................... # Response DTOs
│   └── UserDto.php
│
├── Foundation/.................... # Base classes and common functionality
│   └── BaseData.php
│
├── Filament/...................... # Admin panel resources
│   ├── Resources/
│   │   └── UserResource/
│   │       ├── UserResource.php
│   │       └── Pages/
│   │           ├── ListUsers.php
│   │           ├── CreateUser.php
│   │           └── EditUser.php
│   └── Pages/
│       └── Auth/
│           └── Login.php
│
├── Http/
│   ├── Controllers/............... # Thin coordination layer
│   │   └── UserController.php
│   └── Policies/.................. # Authorization policies
│       └── UserPolicy.php
│
├── Models/......................... # Eloquent models
│   └── User.php
│
├── Providers/..................... # Service providers
│   ├── AppServiceProvider.php
│   └── Filament/
│       └── AdminPanelProvider.php
│
database/
├── factories/..................... # Model factories
│   └── UserFactory.php
├── migrations/.................... # Database migrations
│   ├── *_create_users_table.php
│   ├── *_create_permission_tables.php
│   └── *_create_oauth_*.php
├── seeders/....................... # Database seeders
│   ├── DatabaseSeeder.php
│   ├── RoleSeeder.php
│   └── UserSeeder.php
│
tests/
├── Feature/....................... # Feature tests
│   ├── UserControllerTest.php
│   └── UserTest.php
└── Unit/.......................... # Unit tests
    ├── ArchitectureTest.php
    ├── BaseDataTest.php
    └── UserPolicyTest.php
```

</details>

<details>
<summary><strong>🔐 Authentication & Authorization</strong></summary>

### **OAuth2 with Passport**
- API authentication using Laravel Passport
- Token-based authentication for API requests
- Secure token management and revocation

### **Role-Based Access Control**
- **Admin Role**: Full access to all resources
- **User Role**: Restricted access based on UserPolicy
- **Policy Enforcement**: Automatic authorization checks

### **UserPolicy Rules**
```php
class UserPolicy
{
    public function viewAny(): bool { return false; }
    public function view(User $user, User $model): bool { return $user->id === $model->id; }
    public function create(): bool { return false; }
    public function update(User $user, User $model): bool { return $user->id === $model->id; }
    public function delete(): bool { return false; }
}
```

</details>

<details>
<summary><strong>🎯 Current Features</strong></summary>

### **User Management**
- ✅ Complete CRUD operations for users
- ✅ Role-based access control
- ✅ Email and password validation
- ✅ Filtering by username and email
- ✅ Pagination support (configurable per_page and page parameters)

### **Admin Panel (Filament)**
- ✅ Beautiful admin interface for user management
- ✅ User listing with search and filters
- ✅ User creation and editing forms
- ✅ Role and permission management
- ✅ Responsive design with Tailwind CSS

### **API Endpoints**
- `GET /api/v1/users` - List users with filtering and pagination (admin only)
- `GET /api/v1/users/{id}` - Show user (own profile or admin)
- `POST /api/v1/users` - Create user (admin only)
- `PUT /api/v1/users/{id}` - Update user (own profile or admin)
- `DELETE /api/v1/users/{id}` - Delete user (admin only)
- `GET /api/v1/user` - Get current authenticated user

### **Query Parameters**
- `username` - Filter users by username (partial match)
- `email` - Filter users by email (exact match)
- `per_page` - Number of items per page (1-100, default: 10)
- `page` - Page number (default: 1)

### **Testing**
- ✅ Comprehensive test coverage (129 tests, 386 assertions)
- ✅ 100% type coverage across all classes
- ✅ Admin and normal user scenarios
- ✅ Authorization testing
- ✅ Validation testing
- ✅ Error handling testing
- ✅ Architecture compliance testing
- ✅ Unit tests for UserPolicy and the BaseData foundation

### **Code Quality**
- ✅ Strict typing throughout the application
- ✅ Automated code formatting with Laravel Pint
- ✅ Static analysis with Larastan
- ✅ Automated refactoring with Rector
- ✅ Prettier formatting for frontend assets
- ✅ BaseData foundation for consistent DTO behavior

</details>