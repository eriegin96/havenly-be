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


### APIs
API List is in `api.json`. Import to Hoppscotch to use it.

- After logged in as user or admin, copy token
- Right click on folder havenly on the right side bar, click on 'Properties', click 'Authorization'
- Paste the token in to 'Token' field
- If logged in as 'user', you can call Users API
- If logged in as 'admin, you can call Admin API
- If no logged in, you can only call Public API


---

## 📝 Important Notes

1. **Date Formats**: All dates should be in `YYYY-MM-DD` format for input
2. **DateTime Response**: All datetime fields are returned in ISO 8601 format with UTC timezone
3. **Pagination**: Available on list endpoints with `page` and `per_page` parameters
4. **File Uploads**: Room images should be handled via multipart/form-data
5. **Authentication**: Use `Authorization: Bearer {token}` header
6. **Content Type**: Always include `Content-Type: application/json` for JSON requests
7. **Accept Header**: Include `Accept: application/json` for JSON responses
8. **Role Middleware**: Admin routes require admin role, user routes require user role
9. **Currency**: All monetary values are in Vietnamese Dong (VND)
10. **Foreign Keys**: When booking, ensure room_type_id exists and has available capacity

## 🔄 Response Status Codes Summary

-   **200 OK**: Successful GET, PUT requests
-   **201 Created**: Successful POST requests (resource created)
-   **400 Bad Request**: Invalid request data or business logic violation
-   **401 Unauthorized**: Missing or invalid authentication token
-   **403 Forbidden**: Valid authentication but insufficient permissions
-   **404 Not Found**: Requested resource doesn't exist
-   **422 Unprocessable Entity**: Validation errors in request data
-   **500 Internal Server Error**: Unexpected server error

## 🛡️ Security Features

-   Laravel Sanctum token-based authentication
-   Role-based access control (RBAC)
-   CSRF protection disabled for API routes
-   Input validation and sanitization
-   Password hashing using bcrypt
-   SQL injection prevention through Eloquent ORM

---

**For support or questions, please contact the development team.**
