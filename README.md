# Time Log & Leave Management System

A comprehensive Laravel-based web application for tracking work time logs and managing leave applications with real-time validation and conflict detection.

## Features

✅ **Time Log Management**
- Record daily work tasks with date selection
- Assign tasks to projects  
- Track time in hours and minutes format (HH:MM)
- Enforce 10-hour daily work limit
- Prevent duplicate task entries
- View and edit existing time logs
- Real-time daily total calculation

✅ **Leave Management**
- Apply for leave with start and end dates
- Automatic conflict detection with work reports
- Prevent overlapping leaves and work logs
- View leave application status (Pending/Approved/Rejected)
- Edit pending leave applications
- Cancel leave applications

✅ **Project Management**
- Static list of 8 pre-configured projects
- Project descriptions
- Active/Inactive project status

✅ **Authentication**
- Simple login system
- Session management
- Logout functionality

---

## System Requirements

- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Composer**: Latest version
- **Node.js**: 14.0 or higher (optional, for frontend assets)

---

## Installation Guide

### Step 1: Clone or Download Project

```bash
https://github.com/rakeshsinghvijay151285-max/laravel-timeform
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Copy Environment File

```bash
copy .env.example .env
```

Or manually create `.env` file with your database configuration:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_taskform
DB_USERNAME=root
DB_PASSWORD=
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Create Database

Create a MySQL database named `laravel_taskform`:

```sql
CREATE DATABASE laravel_taskform;
```

### Step 6: Run Migrations

This will create all necessary database tables:

```bash
php artisan migrate
```

### Step 7: Seed Database

Populate the database with test data:

```bash
php artisan db:seed
```

**Seeded Data Includes:**
- **Test User Account**
  - Email: `test@example.com`
  - Password: `password`

- **8 Sample Projects:**
  - Website Redesign
  - Mobile App Development
  - Database Migration
  - API Integration
  - Security Audit
  - Performance Optimization
  - Testing & QA
  - Documentation

- **Sample Time Logs** (for the test user)
- **Sample Leave Application** (for the test user)

---

## Running the Application

### Start Development Server

```bash
php artisan serve
```

The application will be available at: **http://localhost:8000**

---

## Login Credentials

**Email:** `test@example.com`  
**Password:** `password`

---

## Application Workflow

### 1. Login to Application

1. Visit `http://localhost:8000`
2. Click on login form
3. Enter test credentials
4. Click "Login" button

### 2. Add Time Logs

1. Click **"Add Time Log"** in navigation
2. Select **Work Date** (must be today or past date)
3. For each task:
   - Select a **Project** from dropdown
   - Enter **Time** in HH:MM format (e.g., 2:30, 10:00)
   - Add **Task Description** (3-500 characters)
   - Click **"+ Add Task"** for multiple tasks
4. Maximum daily limit: **10 hours**
5. Click **"Submit Time Log"** to save

### 3. View Time Logs

1. Click **"View Logs"** in navigation
2. See all your time logs in a table format
3. Click **eye icon** to view details
4. Click **pencil icon** to edit
5. Click **trash icon** to delete

### 4. Apply for Leave

1. Click **"Apply Leave"** in navigation
2. Select **Start Date** (must be today or future date)
3. Select **End Date** (must be after start date)
4. Add optional **Reason**
5. System will automatically check for conflicting work reports
6. Click **"Submit Leave Application"**

### 5. View Leaves

1. Click **"My Leaves"** in navigation
2. View all leave applications with status
3. Edit or delete pending applications
4. See summary statistics

---

## Database Tables

### users
- User account information
- Authentication data

### projects
- Project master data
- Project descriptions
- Status (active/inactive)

### time_logs
- Work date
- Project association
- Task description
- Hours and minutes
- User association

### leaves
- Leave period (start_date to end_date)
- Reason
- Status (pending/approved/rejected)
- User association

---

## API Endpoints (AJAX)

### Get Daily Total Time Logs

```
GET /time-logs/daily-total?work_date=YYYY-MM-DD
```

Returns: JSON with total hours, minutes, and existing logs

### Check Leave Conflicts

```
GET /leaves/check-conflict?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
```

Returns: JSON with conflict status and conflicting dates

---


## Troubleshooting

### Issue: "Could not open input file: artisan"

**Solution:** Make sure you're in the correct directory:
```bash
cd e:\projects\taskform\taskform
php artisan serve
```

### Issue: "SQLSTATE[HY000]: General error: 1030"

**Solution:** Run migrations again:
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Issue: Login not working

**Solution:** Check if database is seeded:
```bash
php artisan db:seed
```

**Test Credentials:**
- Email: `test@example.com`
- Password: `password`
