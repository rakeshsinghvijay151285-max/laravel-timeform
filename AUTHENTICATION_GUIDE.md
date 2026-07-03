# Complete Authentication System - Line-by-Line Explanation

## Overview
This document provides a detailed, line-by-line explanation of all authentication modules, libraries, configuration files, and how they work together in the Laravel 11 Time Log & Leave Management System.

---

## Table of Contents
1. [Core Libraries & Dependencies](#core-libraries--dependencies)
2. [Configuration Files](#configuration-files)
3. [Model Layer](#model-layer)
4. [Middleware Layer](#middleware-layer)
5. [Routes](#routes)
6. [Views](#views)
7. [Authentication Flow](#authentication-flow)
8. [Session Management](#session-management)

---

## Core Libraries & Dependencies

### Laravel Framework (10.x)
**File**: `composer.json`

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        ...
    }
}
```

**Explanation**:
- **laravel/framework**: The main Laravel framework that provides all built-in authentication features including Auth facade, guards, providers, policies
- **php**: Requires PHP 8.2+ which is needed for Laravel 11

### Authentication-Related Built-in Classes

Laravel includes several core authentication classes automatically:

| Class | Location | Purpose |
|-------|----------|---------|
| `Illuminate\Auth\Middleware\Authenticate` | Laravel Core | Base middleware that checks if user is authenticated |
| `Illuminate\Foundation\Auth\User as Authenticatable` | Laravel Core | Base class for authenticatable models |
| `Illuminate\Support\Facades\Auth` | Laravel Core | Auth facade for accessing authentication methods |
| `Illuminate\Auth\GuardHelpers` | Laravel Core | Helper methods for guards (web, api) |

---

## Configuration Files

### 1. `config/auth.php` - Authentication Configuration

#### Lines 1-12: Configuration Header
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application.
    |
    */
```

**Explanation**:
- Line 1: `<?php` - Open PHP tag
- Line 3: `return [` - This file returns an array of authentication configuration
- Lines 5-12: Comment block explaining this section

#### Lines 14-17: Default Guard and Password Provider
```php
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
```

**Line-by-Line**:
- Line 14: `'defaults' => [` - Define default settings for authentication
- Line 15: `'guard' => 'web'` - Use 'web' guard by default (session-based, not API tokens)
- Line 16: `'passwords' => 'users'` - Use 'users' configuration for password resets
- Line 17: `],` - Close defaults array

**What it means**:
- When you call `auth()->check()` or `Auth::attempt()`, Laravel uses the 'web' guard
- The 'web' guard uses session storage (cookies) to maintain login state
- Alternative guards: 'api' uses token-based authentication

#### Lines 40-47: Authentication Guards Configuration
```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
```

**Line-by-Line**:
- Line 40: `'guards' => [` - Define available authentication guards
- Line 41: `'web' => [` - Define the 'web' guard (used for session-based auth)
- Line 42: `'driver' => 'session'` - Use PHP sessions to store authentication state
  - **'session'**: Stores auth info in PHP $_SESSION (server-side), user ID kept in session
  - Alternatives: 'token' for API tokens, 'oauth' for OAuth
- Line 43: `'provider' => 'users'` - Use 'users' provider to retrieve user from database
  - This tells Laravel: "When authenticating, look in the 'users' provider"

**How it works**:
```
User Login → Auth::attempt($credentials)
  ↓
Laravel checks 'web' guard with 'session' driver
  ↓
Looks up user using 'users' provider (Eloquent model)
  ↓
If credentials match, stores user ID in $_SESSION
  ↓
On next request, PHP loads session and sets Auth::user()
```

#### Lines 62-70: User Providers Configuration
```php
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],
```

**Line-by-Line**:
- Line 62: `'providers' => [` - Define where to find users in your system
- Line 63: `'users' => [` - Define 'users' provider (referenced in guards)
- Line 64: `'driver' => 'eloquent'` - Use Eloquent ORM (not raw database queries)
  - **'eloquent'**: Query users through `App\Models\User` model
  - Alternative: `'database'` - Query `users` table directly without model
- Line 65: `'model' => App\Models\User::class` - Use this model for user lookups
  - When `Auth::attempt()` is called, Laravel queries: `User::where('email', $email)->first()`

#### Lines 89-96: Password Reset Configuration
```php
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
```

**Line-by-Line**:
- Line 89: `'passwords' => [` - Configuration for password reset functionality
- Line 91: `'provider' => 'users'` - Use users provider when resetting passwords
- Line 92: `'table' => 'password_reset_tokens'` - Store reset tokens in this table
- Line 93: `'expire' => 60` - Reset tokens expire after 60 minutes
- Line 94: `'throttle' => 60` - Can only request reset token every 60 seconds

#### Lines 105-107: Password Confirmation Timeout
```php
    'password_timeout' => 10800,
```

**Explanation**:
- `10800` seconds = 3 hours
- When user accesses sensitive operations, they must confirm password
- Password confirmation expires after 3 hours of inactivity

---

## Model Layer

### `app/Models/User.php` - User Model

#### Lines 1-9: Namespace and Imports
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
```

**Line-by-Line**:
- Line 1: `<?php` - PHP opening tag
- Line 3: `namespace App\Models;` - Namespace for this class
- Line 5: `use Illuminate\Database\Eloquent\Factories\HasFactory;` - Import factory trait for testing
- Line 6: `use Illuminate\Foundation\Auth\User as Authenticatable;` - **CRITICAL** for authentication
  - `Authenticatable` provides: password hashing, auth methods, `getAuthIdentifier()`
  - This base class extends `Model` and implements `Authenticatable` interface
- Line 7: `use Illuminate\Notifications\Notifiable;` - Allows sending notifications (email, SMS, etc.)
- Line 8: `use Laravel\Sanctum\HasApiTokens;` - For API token authentication (not used in this project)

#### Lines 11-12: Class Declaration and Traits
```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
```

**Line-by-Line**:
- Line 11: `class User extends Authenticatable` - **KEY**: Extends `Authenticatable`
  - This base class provides authentication methods like:
    - `getAuthIdentifier()` - Returns the unique ID for sessions
    - `getAuthPassword()` - Returns hashed password for comparison
    - `getRememberToken()` / `setRememberToken()` - "Remember me" functionality
- Line 12: `use HasApiTokens, HasFactory, Notifiable;` - Apply traits
  - `HasApiTokens`: Provides `createToken()` for generating API tokens
  - `HasFactory`: Allows `User::factory()->create()` in tests
  - `Notifiable`: Allows `$user->notify()` for sending notifications

#### Lines 18-23: Protected $fillable Property
```php
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
```

**Explanation**:
- `$fillable` - Whitelist of attributes that can be mass-assigned via `User::create([])`
- **Mass Assignment Protection**: Prevents accidental assignment of sensitive attributes
- Example allowed: `User::create(['name' => 'John', 'email' => 'john@example.com', 'password' => 'hashed'])`
- Example blocked: `User::create(['is_admin' => true])` - would be ignored

#### Lines 30-34: Protected $hidden Property
```php
    protected $hidden = [
        'password',
        'remember_token',
    ];
```

**Explanation**:
- `$hidden` - Attributes excluded from JSON/array representation
- When returning `auth()->user()` as JSON, password is never included
- Prevents accidental password exposure in API responses
- Example: `response()->json(auth()->user())` won't include password

#### Lines 40-42: Protected $casts Property
```php
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
```

**Explanation**:
- `$casts` - Automatic type conversion for attributes
- `'email_verified_at' => 'datetime'` - Convert to Carbon datetime object
- Use: `$user->email_verified_at->format('Y-m-d')` instead of string manipulation

#### Lines 44-48: Relationships
```php
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
```

**Explanation**:
- `timeLogs()` - One user has many time logs
  - Use: `auth()->user()->timeLogs()` to get all time logs for current user
  - SQL: `SELECT * FROM time_logs WHERE user_id = ?`
- `leaves()` - One user has many leave applications
  - Use: `auth()->user()->leaves()` to get all leaves for current user

---

## Middleware Layer

### `app/Http/Middleware/Authenticate.php` - Authentication Middleware

#### Complete File
```php
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
```

**Line-by-Line**:

- Line 1: `<?php` - PHP opening tag

- Line 3: `namespace App\Http\Middleware;` - Namespace

- Line 5: `use Illuminate\Auth\Middleware\Authenticate as Middleware;` - Import base Authenticate middleware
  - `Middleware` is Laravel's built-in authentication middleware
  - Checks if `auth()->check()` returns true
  - If false, calls `redirectTo()` to determine where to send user

- Line 6: `use Illuminate\Http\Request;` - Import Request class

- Line 8: `class Authenticate extends Middleware` - Extend Laravel's built-in Authenticate middleware
  - We override only the `redirectTo()` method
  - All other authentication logic inherited from parent

- Line 13: `protected function redirectTo(Request $request): ?string` - Method that Laravel calls when user is not authenticated
  - **Type hint** `Request` - Parameter is HTTP request object
  - **Return type** `?string` - Returns string (redirect route) or null
  - **Nullable return**: `?string` means can return null

- Line 15: `return $request->expectsJson() ? null : route('login');` - Ternary operator
  - `$request->expectsJson()` - Returns true if request expects JSON (API request)
  - If true: return `null` (don't redirect API requests, let them fail normally)
  - If false: return `route('login')` (redirect browser requests to login page)
  - **Why?** API clients get HTTP 401 error (more useful than HTML redirect)

### `app/Http/Middleware/RedirectIfAuthenticated.php` - Guest Middleware

This middleware is referenced in `Kernel.php` but enforces that only guests can access login/register pages.

```php
// Pseudo code structure:
class RedirectIfAuthenticated extends Middleware {
    public function handle($request, $next) {
        if (auth()->check()) {  // If already logged in
            return redirect('/time-logs');  // Redirect to dashboard
        }
        return $next($request);  // Otherwise continue to login form
    }
}
```

**Used in routes**: `Route::post('/login', ...)->middleware('guest')`
- Prevents logged-in users from accessing login form

---

## Middleware Registration

### `app/Http/Kernel.php` - HTTP Kernel (Middleware Configuration)

#### Lines 44-55: Web Middleware Group
```php
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
```

**Explanation** (middleware runs in order):

1. **EncryptCookies** - Encrypt all cookies before sending to client
   - Why? Prevent tampering with session cookie

2. **AddQueuedCookiesToResponse** - Add queued cookies to response
   - Used by Laravel internally for cookie management

3. **StartSession** - **CRITICAL for authentication**
   - Loads session from cookie: `session_id` → retrieve `$_SESSION` from storage
   - Enables `auth()->check()` to work by restoring user from session
   - Flow:
     ```
     1. Browser sends request with: Cookie: PHPSESSID=abc123
     2. StartSession middleware loads session data
     3. If session contains user_id, restores that user
     4. auth()->user() now works
     5. On response, saves any session changes back to storage
     ```

4. **ShareErrorsFromSession** - Share validation errors with views
   - Makes `$errors` variable available in all Blade templates
   - Used for showing validation messages: `@if($errors->any())`

5. **VerifyCsrfToken** - **Security**: Verify CSRF tokens on POST/PUT/DELETE
   - Prevents cross-site request forgery attacks
   - Every POST form needs `@csrf` token
   - Why? Prevents malicious sites from making requests as authenticated user

6. **SubstituteBindings** - Route model binding
   - Automatically converts route parameters to models
   - Example: `Route::get('/users/{user}')` automatically loads User from {user}

#### Lines 66-74: Middleware Aliases
```php
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ...
    ],
```

**Line-by-Line**:

- `'auth'` => Points to our custom `Authenticate` class
  - Used as: `Route::get(...)->middleware('auth')`
  - Checks if user is logged in, redirects to login if not

- `'guest'` => Points to `RedirectIfAuthenticated`
  - Used as: `Route::get('/login')->middleware('guest')`
  - Checks if user is logged in, redirects to dashboard if yes
  - Only allows non-authenticated users to access

- `'verified'` => Email verification middleware
  - Checks if `$user->email_verified_at` is not null
  - Not used in this project but available

---

## Routes

### `routes/web.php` - Web Routes with Authentication

#### Lines 1-10: Home Route
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\LeaveController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/time-logs');
    }
    return view('welcome');
});
```

**Line-by-Line**:

- Line 1: `<?php` - PHP opening tag

- Line 3: `use Illuminate\Support\Facades\Route;` - Import Route facade
  - `Route::get()`, `Route::post()`, etc. provided by this

- Line 4-5: Import controller classes
  - Makes `TimeLogController::class` available in route definitions

- Line 7: `Route::get('/', function () {` - GET route for home page
  - `'/'` - Root path
  - Anonymous function handler

- Line 8: `if (auth()->check())` - Check if user is authenticated
  - `auth()` - Get the Auth facade
  - `check()` - Returns true if user is logged in, false if guest
  - Equivalent to: `Auth::check()` or `\\Illuminate\\Support\\Facades\\Auth::check()`

- Line 9: `return redirect('/time-logs');` - Redirect logged-in users to dashboard

- Line 10-11: `return view('welcome');` - Show welcome page for guests

#### Lines 13-18: Login GET Route
```php
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');
```

**Line-by-Line**:

- `Route::get('/login', function () {` - GET route for login page
  - Shows login form to user

- `return view('auth.login');` - Render login form view

- `->name('login')` - Route name (used in redirects and forms)
  - Allows `route('login')` instead of hardcoding `/login`
  - **Why?** Easy to change URL later without updating all references
  - Used in: `Authenticate::redirectTo()` returns `route('login')`

- `->middleware('guest')` - Restrict to guests only
  - If user is already logged in, redirect to dashboard
  - Prevents logged-in user from seeing login form

#### Lines 20-36: Login POST Route
```php
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/time-logs');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware('guest');
```

**This is the CORE authentication logic. Let's break it down:**

- Line 20: `Route::post('/login', function (\Illuminate\Http\Request $request) {`
  - POST request handler (form submission)
  - Type hint: `\Illuminate\Http\Request` - Full namespace specified

- Line 21-24: `$credentials = $request->validate([...])`
  - **Validation**: Check that email and password are provided
  - `'email' => 'required|email'` - Email is required and valid format
  - `'password' => 'required'` - Password is required (at least 1 character)
  - If validation fails: Throws `ValidationException` (caught by Laravel, returns to form with errors)
  - If validation passes: Returns array `['email' => 'user@example.com', 'password' => 'password123']`

- Line 26: `if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {`
  - **Authentication Logic**:
    - `Auth::attempt()` receives credentials
    - Queries: `User::where('email', $credentials['email'])->first()`
    - Checks: `Hash::check($credentials['password'], $user->password)`
    - If both match:
      1. Stores user ID in session
      2. Returns true
    - If no match: Returns false

- Line 27: `$request->session()->regenerate();`
  - **Security**: Generate new session ID
  - Prevents session fixation attacks
  - Why? If attacker knew old session ID, they're locked out
  - Process:
    ```
    Old session ID: abc123
    After regenerate:
    1. Copy user data to new session
    2. Issue new session ID: xyz789
    3. Delete old session abc123
    4. Send new cookie with xyz789
    ```

- Line 28: `return redirect('/time-logs');`
  - Redirect to dashboard after successful login

- Line 31-34: Error handling
  - `return back()` - Return to previous page (login form)
  - `->withErrors([...])` - Attach error messages to session
  - `->onlyInput('email')` - Preserve email field (don't show password for security)
  - Error displays in view: `@error('email') {{ $message }} @enderror`

- Line 35: `->middleware('guest')` - Only guests can POST to login

#### Lines 37-42: Logout Route
```php
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
```

**Line-by-Line**:

- Line 37: `Route::post('/logout', ...)` - POST route (not GET, to prevent CSRF)
  - Why POST? GET requests can be triggered by just clicking a link
  - POST requires CSRF token from form, more secure

- Line 38: `\Illuminate\Support\Facades\Auth::logout();` - Clear authentication
  - Removes user ID from session
  - `$user = auth()->user();` now returns null

- Line 39: `$request->session()->invalidate();`
  - **Destroy entire session**
  - Deletes all session data (not just user ID)
  - Why? If session was compromised, start fresh

- Line 40: `$request->session()->regenerateToken();`
  - **Regenerate CSRF token**
  - Why? Old token could be cached by attacker

- Line 41: `return redirect('/');` - Redirect to home page

- Line 42: `->name('logout')` - Route name used in forms

#### Lines 44-57: Protected Routes (Authenticated Users Only)
```php
Route::middleware(['auth'])->group(function () {
    Route::redirect('/dashboard', '/time-logs');

    // Time Log Routes
    Route::get('time-logs/daily-total', [TimeLogController::class, 'getDailyTotal'])->name('time-logs.daily-total');
    Route::resource('time-logs', TimeLogController::class);

    // Leave Routes
    Route::get('leaves/check-conflict', [LeaveController::class, 'checkConflict'])->name('leaves.check-conflict');
    Route::resource('leaves', LeaveController::class);
});
```

**Line-by-Line**:

- Line 44: `Route::middleware(['auth'])->group(function () {`
  - **Apply 'auth' middleware to all routes in group**
  - Only authenticated users can access routes here
  - If guest tries to access: Redirected to login by `Authenticate` middleware

- Line 45: `Route::redirect('/dashboard', '/time-logs');`
  - Convenience redirect (legacy naming)

- Line 48: `Route::get('time-logs/daily-total', ...)`
  - Specific route defined **BEFORE** resource route
  - **Why?** If defined after resource route, would match resource's `show` method
  - Used for AJAX: Gets daily total for a specific date

- Line 49: `Route::resource('time-logs', TimeLogController::class);`
  - **Resource routes** - Shorthand for CRUD routes:
    ```
    GET    /time-logs              → index   (list all)
    GET    /time-logs/create       → create  (show form)
    POST   /time-logs              → store   (save)
    GET    /time-logs/{id}         → show    (view one)
    GET    /time-logs/{id}/edit    → edit    (show edit form)
    PUT    /time-logs/{id}         → update  (save changes)
    DELETE /time-logs/{id}         → destroy (delete)
    ```
  - Equivalent to writing all 7 routes manually

- Line 52: `Route::get('leaves/check-conflict', ...)`
  - Specific route **before** resource, same reason as above
  - AJAX endpoint to check if dates conflict with work logs

- Line 53: `Route::resource('leaves', LeaveController::class);`
  - Same 7 resource routes for leave management

---

## Views

### `resources/views/auth/login.blade.php` - Login Form

#### Lines 1-40: HTML Head and Styling
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Time Log & Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
```

**Line-by-Line**:

- Line 1: `<!DOCTYPE html>` - HTML5 declaration

- Line 2: `<html lang="en">` - HTML root, language = English

- Line 4: `<meta charset="UTF-8">` - Character encoding (UTF-8 for international characters)

- Line 5: `<meta name="viewport"...>` - Mobile responsiveness
  - `width=device-width` - Responsive width
  - `initial-scale=1.0` - Don't zoom on load

- Line 6: `<title>...</title>` - Browser tab title

- Line 7: `<link ... bootstrap@5.3.0...css>` - Bootstrap CSS
  - Provides responsive grid, form styling, buttons
  - CDN: Content Delivery Network (fast loading from distributed servers)

#### Lines 55-75: Form Submission
```html
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
```

**Line-by-Line**:

- Line 55: `<form method="POST" action="{{ route('login') }}">`
  - `method="POST"` - Send form as POST (not GET)
  - `action="{{ route('login') }}"` - Submit to login POST route
  - `{{ route('login') }}` - Blade syntax, generates: `/login`

- Line 56: `@csrf` - Blade directive for CSRF token
  - Generates hidden input: `<input name="_token" value="...token...">`
  - Sent with form, verified by `VerifyCsrfToken` middleware
  - Prevents cross-site request forgery

- Line 59: `<label for="email"...>` - Label linked to email input
  - `for="email"` - Connects to `<input id="email">`
  - Clicking label focuses input (better UX)

- Line 60: `<input type="email" ... @error('email') is-invalid @enderror ...>`
  - `type="email"` - Browser validates email format
  - `@error('email')` - Blade directive, if validation error on email:
    - Adds `is-invalid` class (red border)
  - `name="email"` - Sent in POST request as `$request->input('email')`
  - `value="{{ old('email') }}"` - **Flash value** - Show submitted email
    - `old('email')` returns last submitted value (from session)
    - Allows user to correct mistakes without retyping
  - `required` - HTML5 validation (before submission)
  - `autofocus` - Focus this field on page load

#### Lines 76-82: Password Input
```html
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
```

**Explanation**:
- `type="password"` - Hides characters as user types (security)
- `name="password"` - Sent as `$request->input('password')`
- Note: No `value="{{ old('password') }}"` - Never flash password field
  - Security: Even if submitted, don't show password in HTML

#### Lines 83-92: Submit Button and Error Display
```html
                        <button type="submit" class="btn btn-login w-100">
                            Login
                        </button>
                    </div>
                </form>

                <div class="test-credentials">
                    <p><strong>Test Credentials:</strong></p>
```

**Line-by-Line**:

- Line 83: `<button type="submit" ...>`
  - `type="submit"` - Submits form to route action
  - When clicked:
    1. Browser validates required fields
    2. Sends POST request with form data
    3. Server receives in `Route::post('/login', ...)`

- Lines 89-92: Display test credentials for development
  - Helps developers test without creating user account
  - Should be removed in production

---

### `resources/views/layouts/app.blade.php` - Master Layout

#### Lines 1-7: HTML Head Section
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
```

**Key Line**:

- Line 6: `<meta name="csrf-token" content="{{ csrf_token() }}">`
  - Stores CSRF token in HTML meta tag
  - Used by JavaScript AJAX requests:
    ```javascript
    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/api/endpoint', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token }
    })
    ```

#### Lines 116-140: Navigation Bar
```html
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('time-logs.index') }}">⏱️ Time Log & Leave</a>
            ...
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('time-logs.create') }}">Add Time Log</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('time-logs.index') }}">View Logs</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
```

**Key Lines**:

- Line 130: `{{ auth()->user()->name }}`
  - Gets current logged-in user's name
  - Only works because middleware has already verified authentication

- Line 139: `<form method="POST" action="{{ route('logout') }}">`
  - Logout form that POST's to logout route

- Line 140: `@csrf` - Include CSRF token
  - Required for logout POST request

- Line 141-142: Submit button styled as dropdown item

#### Lines 161-173: Error Display
```html
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Error!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
```

**Explanation**:

- Line 161: `@if($errors->any())`
  - Check if there are validation errors
  - `$errors` is available in all views (from `ShareErrorsFromSession` middleware)

- Line 165: `@foreach($errors->all() as $error)`
  - Loop through all error messages
  - `$errors->all()` returns array of all errors
  - Example: `['Email is invalid', 'Password is required']`

- Line 166: `{{ $error }}` - Echo error message

---

## Authentication Flow

### Complete Login Flow (Sequence Diagram)

```
1. USER VISITS PAGE
   └─ Browser: GET /login
   └─ Route: Route::get('/login', ...) returns auth.login view
   └─ Middleware: 'guest' middleware checks - user is not logged in ✓
   └─ Response: HTML login form displayed

2. USER SUBMITS FORM
   └─ Browser: POST /login with email=test@example.com, password=password
   └─ Middleware: 'guest' middleware checks - user is not logged in ✓
   └─ Middleware: VerifyCsrfToken checks @csrf token matches
   └─ Handler function receives request

3. VALIDATION
   └─ Code: $request->validate(['email' => 'required|email', 'password' => 'required'])
   └─ Check: Email is provided and valid format ✓
   └─ Check: Password is provided ✓
   └─ Result: $credentials = ['email' => 'test@example.com', 'password' => 'password']

4. AUTHENTICATION
   └─ Code: Auth::attempt($credentials)
   └─ Query: User::where('email', 'test@example.com')->first()
   └─ Found: User object retrieved from database
   └─ Check: Hash::check('password', $user->password)
   │  └─ Compare plain text 'password' with hashed 'password' in database
   │  └─ PHP bcrypt algorithm: If matches ✓
   └─ Action: auth()->login($user) called internally
   │  └─ Stores $user->id in $_SESSION['user_id']
   │  └─ Returns true
   └─ Result: Authentication successful

5. SESSION REGENERATION
   └─ Code: $request->session()->regenerate()
   └─ Action: Generate new session ID
   └─ Reason: Security - session fixation prevention
   └─ Cookie: browser receives Set-Cookie: PHPSESSID=new_id

6. REDIRECT
   └─ Code: return redirect('/time-logs')
   └─ Response: HTTP 302 redirect to /time-logs

7. USER VISITS DASHBOARD
   └─ Browser: GET /time-logs (with cookie PHPSESSID=...)
   └─ Middleware: StartSession loads session data from storage
   │  └─ Retrieves: $_SESSION['user_id'] = 1
   │  └─ Action: Internally calls Auth::onceUsingId(1)
   │  └─ Effect: auth()->check() now returns true, auth()->user() returns User object
   └─ Middleware: 'auth' middleware checks auth()->check() ✓
   └─ Route: Executes time-logs index action
   └─ Response: Dashboard displayed with {{ auth()->user()->name }}
```

### Logout Flow

```
1. USER CLICKS LOGOUT
   └─ Browser: POST /logout with CSRF token
   └─ Route: Route::post('/logout', ...)

2. AUTHENTICATION CLEARED
   └─ Code: Auth::logout()
   └─ Action: Unset $_SESSION['user_id']
   └─ Result: auth()->check() returns false

3. SESSION DESTROYED
   └─ Code: $request->session()->invalidate()
   └─ Action: Delete entire session from storage
   └─ Reason: If session was compromised, start fresh

4. CSRF TOKEN REGENERATED
   └─ Code: $request->session()->regenerateToken()
   └─ Action: Generate new _token
   └─ Reason: Old token could be cached/compromised

5. REDIRECT TO HOME
   └─ Code: return redirect('/')
   └─ Response: HTTP 302 to home page

6. BROWSER NEXT REQUEST
   └─ Browser: GET / (without session cookie or with deleted session)
   └─ Result: Guest sees welcome page or redirects to login
```

---

## Session Management

### How Sessions Work

**Session Storage Lifecycle**:

```
1. FIRST REQUEST (New User)
   ├─ StartSession middleware checks for session cookie
   ├─ No cookie found → Create new session ID
   ├─ Store empty $_SESSION = []
   └─ Send cookie: Set-Cookie: PHPSESSID=abc123

2. LOGIN PROCESS
   ├─ Auth::attempt() sets $_SESSION['user_id'] = 1
   ├─ Middleware saves session to storage (files, database, etc)
   ├─ Response sent to browser with PHPSESSID=abc123

3. NEXT REQUEST (User Returns)
   ├─ Browser sends: Cookie: PHPSESSID=abc123
   ├─ StartSession middleware retrieves session data
   ├─ $_SESSION loaded from storage: ['user_id' => 1]
   ├─ auth()->check() checks if $_SESSION['user_id'] exists ✓
   ├─ auth()->user() queries: User::find($_SESSION['user_id']) → User object
   └─ Route handler can access auth()->user()

4. LOGOUT PROCESS
   ├─ Auth::logout() unsets $_SESSION['user_id']
   ├─ $request->session()->invalidate() deletes entire session file
   ├─ Browser's PHPSESSID cookie still exists but now empty/invalid
   └─ Next request: No session data → Not authenticated

5. BROWSER CLOSE (Optional)
   ├─ Session cookies are "session cookies" (no expiration date)
   ├─ Browser deletes them when window closes (by default)
   ├─ Even if cookie exists, session file may expire (config)
   └─ Next launch requires re-login
```

### Session Configuration

**File**: `.env` and `config/session.php`

```php
SESSION_DRIVER=file          // Store sessions in files
SESSION_LIFETIME=120         // Session expires after 120 minutes
SESSION_EXPIRE_ON_CLOSE=true // Delete cookie when browser closes
COOKIE_SECURE=false          // false for local (http), true for production (https)
COOKIE_HTTPONLY=true         // Prevent JavaScript access to cookie (security)
COOKIE_SAMESITE=lax          // CSRF protection - cookie only sent from same site
```

**How it works**:
1. **SESSION_DRIVER=file**: Sessions stored in `storage/framework/sessions/`
2. **SESSION_LIFETIME=120**: If file not accessed for 120 min, deleted by garbage collection
3. **COOKIE_HTTPONLY=true**: JavaScript cannot access `document.cookie` (prevents XSS attacks)
4. **COOKIE_SAMESITE=lax**: Cookie only sent when navigating to site directly (prevents CSRF)

---

## Security Best Practices in Authentication

### 1. CSRF Protection
- **How**: `@csrf` directive adds hidden token
- **Why**: Prevents malicious sites from making requests as you
- **In this project**: Every form uses `@csrf`

### 2. Password Hashing
- **How**: Password stored as bcrypt hash, never plain text
- **Check**: `Hash::check($input, $hashed)` compares safely
- **In this project**: Laravel automatically hashes in `User` model

### 3. Session Security
- **Regeneration**: After login, new session ID prevents fixation
- **Invalidation**: After logout, entire session deleted
- **HttpOnly**: JavaScript can't access session cookie
- **SameSite**: Cookie only sent from same website

### 4. Authentication Middleware
- **Protect routes**: `->middleware('auth')` prevents guest access
- **Redirect**: Unauthenticated users redirected to login
- **In this project**: All time-logs and leaves routes protected

### 5. Form Validation
- **Email validation**: `'email' => 'required|email'`
- **Server-side**: Validation happens on server, not just browser
- **Error messages**: Generic "credentials don't match" prevents user enumeration

---

## Common Authentication Queries & Methods

### Check if user is authenticated
```php
if (auth()->check()) {  // Returns true/false
    $user = auth()->user();  // Get User object
}

// Equivalent methods:
Auth::check()
Auth::user()
\Illuminate\Support\Facades\Auth::check()
```

### Get current user
```php
$name = auth()->user()->name;
$email = auth()->user()->email;
$id = auth()->id();  // Get only ID, doesn't load full user
```

### Login a user
```php
// Option 1: Login by credentials
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Login successful
}

// Option 2: Direct login
Auth::login($userObject);

// Option 3: Login by ID
Auth::loginUsingId(1);
```

### Logout
```php
Auth::logout();
```

### Check guard
```php
Auth::guard('web')->check()  // Explicit guard check
Auth::viaRemember()  // Check if logged in via remember token
```

---

## Database Tables Related to Authentication

### users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### password_reset_tokens Table
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP
);
```

These tables are created by migrations:
- `database/migrations/2014_10_12_000000_create_users_table.php`
- `database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php`

---

## Troubleshooting Guide

| Problem | Cause | Solution |
|---------|-------|----------|
| "Route [login] not defined" | No login route named 'login' | Add `->name('login')` to login route |
| "Undefined variable: errors" | 'ShareErrorsFromSession' middleware missing | Check `Kernel.php` web middleware group |
| Login works but stays on form | Session not starting | Check `SESSION_DRIVER` in `.env` |
| Can access protected routes without login | 'auth' middleware missing | Add `->middleware('auth')` to route |
| Password always fails | Password not hashed before saving | Use `Hash::make($password)` |
| Session expires too fast | `SESSION_LIFETIME` too short | Increase in `.env` or `config/session.php` |

---

## Summary

The authentication system works through:

1. **Configuration** (`config/auth.php`): Defines which guard ('web'), driver ('session'), and provider ('eloquent') to use
2. **Model** (`User.php`): Extends Authenticatable to support hashing and auth methods
3. **Middleware** (`Kernel.php`): Registers middleware aliases and groups
4. **Authentication Middleware** (`Authenticate.php`): Checks if user logged in, redirects if not
5. **Routes** (`web.php`): Defines login/logout endpoints and protects routes with `->middleware('auth')`
6. **Views** (`auth/login.blade.php`): Shows form with CSRF token
7. **Session**: Stores user ID after login, retrieves on each request

This is Laravel's standard, battle-tested authentication system used by millions of applications.

