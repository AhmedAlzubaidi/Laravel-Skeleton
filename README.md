# Laravel Skeleton – Project Overview

This project is a reusable **Laravel Skeleton**, crafted as a personal starting point for building Laravel APIs.

While originally built for personal use, this project is **open source and community-friendly** — contributions, ideas, and improvements are welcome to help evolve it into a more flexible and powerful foundation for Laravel API development.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+
- Composer

### Testing Requirements
For running tests and code coverage, you'll need these PHP extensions:
- **ext-pdo_sqlite**: For in-memory database testing
- **ext-xdebug**: For code coverage reports

### Project Installation
```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate Passport encryption keys
php artisan passport:keys

# Run database migrations
php artisan migrate
```

### Development Commands
```bash
# Ide helper commands
php artisan ide-helper:generate
php artisan ide-helper:models --write-mixin

# Run tests
php artisan test

# Run tests with coverage (requires Xdebug)
XDEBUG_MODE=coverage php artisan test --coverage

# Format code
./vendor/bin/pint

# Start development server
php artisan serve
```

---

<details>
<summary><strong>🧱 Architecture & Design Principles</strong></summary>

### **Controller + Commands/Queries Pattern**
- **Controllers**: Controllers for business logic
- **Commands**: DTOs for write operations (create, update) using Spatie Laravel Data
- **Queries**: DTOs for read operations (list, show) using Spatie Laravel Data
- **DTOs**: Data Transfer Objects for responses using Spatie Laravel Data

### **Authorization & Permissions**
- **UserPolicy**: Enforces access control based on user roles
- **Spatie Permission**: Role-based access control
- **Admin Bypass**: Admins bypass all authorization checks via `Gate::before` callback

### **Routes**
- API routes are defined in `routes/api.php`
- Routes are RESTful, structured around resources (e.g., `/api/users`)
- Each route delegates to controller methods, which use Commands/Queries for validation

## 🧩 Example: Users Resource
```php
use App\Http\Controllers\UserController;

Route::middleware('auth:api')->group(function () {
    Route::resource('users', UserController::class)->except(['create', 'edit']);
});
```

## 🧩 Example: UserController with Commands/Queries
```php
class UserController extends Controller
{
    public function index(GetUsersQuery $query)
    {
        Gate::authorize('viewAny', User::class);
        
        $users = User::query()
            ->when($query->name, fn($q, $name) => $q->where('name', 'like', "%{$name}%"))
            ->when($query->email, fn($q, $email) => $q->where('email', 'like', "%{$email}%"))
            ->when($query->status, fn($q, $status) => $q->where('status', $status))
            ->get();

        return response()->json([
            'data' => UserDto::collect($users),
            'message' => __('Users fetched successfully'),
        ]);
    }

    public function store(CreateUserCommand $command)
    {
        Gate::authorize('create', User::class);
        
        $user = User::create($command->toArray());
        
        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User created successfully'),
        ], 201);
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
class CreateUserCommand extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserStatus $status = UserStatus::ACTIVE,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'status' => ['required', new Enum(UserStatus::class)],
        ];
    }
}

// UserDto - Response serialization
class UserDto extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $password,
        public UserStatus $status,
    ) {}
}
```

</details>

<details>
<summary><strong>📦 Core Packages</strong></summary>

### **Core Framework & Authentication**
- [`laravel/framework`](https://laravel.com/) – The Laravel framework
- [`laravel/passport`](https://github.com/laravel/passport) – OAuth2 server for API authentication
- [`spatie/laravel-permission`](https://github.com/spatie/laravel-permission) – Role and permission management

### **Data & Validation**
- [`spatie/laravel-data`](https://github.com/spatie/laravel-data) – Typed DTOs & transformers
- [`spatie/laravel-typescript-transformer`](https://github.com/spatie/laravel-typescript-transformer) – Generate TypeScript types from DTOs

### **Development & IDE Support**
- [`barryvdh/laravel-ide-helper`](https://github.com/barryvdh/laravel-ide-helper) – IDE autocompletion for models, facades etc.
- [`laravel/pint`](https://github.com/laravel/pint) – Opinionated code style formatting

### **Monitoring & Health**
- [`spatie/laravel-health`](https://github.com/spatie/laravel-health) – Health and system checks

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
- **43 tests** covering all CRUD operations and architecture principles
- **207 assertions** ensuring comprehensive coverage
- **Authorization testing** for both admin and normal users
- **Validation testing** for all input fields
- **Error handling** (404, 403, 422 status codes)
- **Architecture compliance** testing

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
├── Enums/......................... # Enum classes
│   └── UserStatus.php
│
├── Http/
│   ├── Controllers/............... # Thin coordination layer
│   │   └── v1/
│   │       └── UserController.php
│   └── Policies/.................. # Authorization policies
│       └── UserPolicy.php
│
├── Models/......................... # Eloquent models
│   └── User.php
│
├── Providers/..................... # Service providers
│   └── AppServiceProvider.php
│
database/
├── factories/..................... # Model factories
│   └── UserFactory.php
├── migrations/.................... # Database migrations
│   └── *_add_status_to_users_table.php
└── seeders/....................... # Database seeders
    ├── DatabaseSeeder.php
    ├── RoleSeeder.php
    └── UserSeeder.php
│
tests/
├── Feature/....................... # Feature tests
│   └── UserControllerTest.php
└── Unit/.......................... # Unit tests
    └── ArchitectureTest.php
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
    public function viewAny(User $user): bool { return false; }
    public function view(User $user, User $model): bool { return $user->id === $model->id; }
    public function create(User $user): bool { return false; }
    public function update(User $user, User $model): bool { return $user->id === $model->id; }
    public function delete(User $user, User $model): bool { return false; }
}
```

</details>

<details>
<summary><strong>🎯 Current Features</strong></summary>

### **User Management**
- ✅ Complete CRUD operations for users
- ✅ Role-based access control
- ✅ User status management (Active, Inactive, Suspended, Pending)
- ✅ Email and password validation
- ✅ Filtering by name, email, and status

### **API Endpoints**
- `GET /api/users` - List users (admin only)
- `GET /api/users/{id}` - Show user (own profile or admin)
- `POST /api/users` - Create user (admin only)
- `PUT /api/users/{id}` - Update user (own profile or admin)
- `DELETE /api/users/{id}` - Delete user (admin only)

### **Testing**
- ✅ Comprehensive test coverage (43 tests, 207 assertions)
- ✅ Admin and normal user scenarios
- ✅ Authorization testing
- ✅ Validation testing
- ✅ Error handling testing
- ✅ Architecture compliance testing

</details>