# 🏨 Havenly Hotel Booking API Documentation

## 📋 Overview

This is a comprehensive REST API for a hotel booking system built with Laravel 12. The API supports authentication, room management, booking operations, reviews, and administrative functions.

**Base URL:** `http://localhost:8000/api`


---

## 🚀 Getting Started

### Prerequisites

1. PHP 8.2+
2. Laravel 12
3. MySQL/PostgreSQL
4. Composer

### Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Run `php artisan key:generate`
5. Run 'Apache server' and 'MySQL' in XAMPP
6. Configure database settings
7. Run `php artisan migrate`
8. Run `php artisan db:seed`
9. Start the server: `php artisan serve`

### Default Credentials

From the seeded data:

-   **Admin**: admin@gmail.com / 123456
-   **User Examples**:
    -   hung@gmail.com / 123456
    -   trung@gmail.com / 123456
    -   huy@gmail.com / 123456
    -   hieu@gmail.com / 123456

**Note:** All seeded users use the password: `123456` (properly hashed in database)