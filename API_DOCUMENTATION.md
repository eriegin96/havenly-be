# 🏨 VietStay Hotel Booking API Documentation

## 📋 Overview

This is a comprehensive REST API for a hotel booking system built with Laravel 12. The API supports authentication, room management, booking operations, and administrative functions.

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

-   After logged in as user or admin, copy token
-   Right click on folder vietstay on the right side bar, click on 'Properties', click 'Authorization'
-   Paste the token in to 'Token' field
-   If logged in as 'user', you can call Users API
-   If logged in as 'admin, you can call Admin API
-   If no logged in, you can only call Public API

---

## 📝 Important Notes

1. **Date Formats**: All dates should be in `YYYY-MM-DD` format for input
2. **DateTime Response**: All datetime fields are returned in ISO 8601 format with UTC timezone
3. **Pagination**: Available on list endpoints with `page` and `limit` parameters
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

## 🔐 Authentication Endpoints

### Register User

**POST** `/auth/register`

**Request Body:**

```json
{
    "name": "string (required, max:100)",
    "email": "string (required, email, max:150, unique)",
    "password": "string (required, min:6, confirmed)",
    "password_confirmation": "string (required)",
    "address": "string (optional, max:255)",
    "phone": "string (optional, max:20)",
    "dob": "date (optional, YYYY-MM-DD)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "address": "123 Main St",
            "phone": "1234567890",
            "dob": "1990-01-01",
            "avatar": null,
            "role": "user",
            "status": 1,
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### Login User

**POST** `/auth/login`

**Request Body:**

```json
{
    "email": "string (required, email)",
    "password": "string (required)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user",
            "status": 1
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### Logout User

**POST** `/auth/logout`

-   **Authentication:** Required
-   **Response (200):** `{"success": true, "message": "Logged out successfully"}`

### Get Profile

**GET** `/auth/profile`

-   **Authentication:** Required
-   **Response (200):** User object

### Update Profile

**PUT** `/auth/profile`

-   **Authentication:** Required

**Request Body:**

```json
{
    "name": "string (optional, max:100)",
    "address": "string (optional, max:255)",
    "phone": "string (optional, max:20)",
    "dob": "date (optional, YYYY-MM-DD)",
    "avatar": "string (optional, max:255)"
}
```

### Change Password

**PUT** `/auth/change-password`

-   **Authentication:** Required

**Request Body:**

```json
{
    "current_password": "string (required)",
    "password": "string (required, min:6, confirmed)",
    "password_confirmation": "string (required)"
}
```

### Upload Image

**POST** `/auth/images/upload`

-   **Authentication:** Required
-   **Content-Type:** `multipart/form-data`

**Request Body:**

```
image: file (required, image, max:5MB, types: jpeg,png,jpg,gif, webp)
```

**Response (201):**

```json
{
    "success": true,
    "message": "Image uploaded successfully",
    "data": {
        "path": "storage/images/avatar/filename.jpg",
        "filename": "filename.jpg",
        "original_name": "original.jpg",
        "size": 12345,
        "mime_type": "image/jpeg"
    }
}
```

---

## 🌐 Public Endpoints

### Facilities

#### Get All Facilities

**GET** `/facilities`

**Response (200):**

```json
{
    "success": true,
    "message": "Facilities retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "WiFi",
            "content": "High-speed internet",
            "description": "Free WiFi throughout the hotel"
        }
    ]
}
```

#### Get Facility by ID

**GET** `/facilities/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Facility retrieved successfully",
    "data": {
        "id": 1,
        "name": "wifi",
        "content": "Wi-Fi",
        "description": "Kết nối Internet tốc độ cao, miễn phí trong toàn bộ khách sạn, giúp bạn dễ dàng làm việc hoặc giải trí trực tuyến."
    }
}
```

### Features

#### Get All Features

**GET** `/features`

**Response (200):**

```json
{
    "success": true,
    "message": "Features retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Air Conditioning",
            "content": "Climate control system"
        }
    ]
}
```

#### Get Feature by ID

**GET** `/features/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Feature retrieved successfully",
    "data": {
        "id": 1,
        "name": "bedroom",
        "content": "Phòng ngủ"
    }
}
```

### Room Types

#### Get All Room Types

**GET** `/room-types`

**Response (200):**

```json
{
    "success": true,
    "message": "Room types retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Deluxe Room",
            "area": 25,
            "price": 500000,
            "quantity": 10,
            "adult": 2,
            "children": 1,
            "description": "Spacious room with city view",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "images": [],
            "facilities": [],
            "features": []
        }
    ]
}
```

#### Get Room Type by ID

**GET** `/room-types/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room type retrieved successfully",
    "data": {
        "id": 1,
        "name": "Phòng Deluxe",
        "area": 30,
        "price": 1000000,
        "quantity": 10,
        "adult": 2,
        "children": 0,
        "description": "Phòng Deluxe với diện tích 30m2, phù hợp cho gia đình hoặc nhóm bạn bè.",
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2024-11-29T00:00:00.000000Z",
        "images": [
            {
                "id": 1,
                "room_type_id": 1,
                "path": "storage/images/rooms/room-1.jpg",
                "is_thumbnail": true,
                "created_at": "2025-09-13 14:40:32"
            }
        ],
        "facilities": [
            {
                "id": 1,
                "name": "wifi",
                "content": "Wi-Fi",
                "description": "Kết nối Internet tốc độ cao, miễn phí trong toàn bộ khách sạn",
                "pivot": { "room_type_id": 1, "facility_id": 1 }
            }
        ],
        "features": [
            {
                "id": 1,
                "name": "bedroom",
                "content": "Phòng ngủ",
                "pivot": { "room_type_id": 1, "feature_id": 1 }
            }
        ]
    }
}
```

### User Queries (Contact)

#### Submit User Query

**POST** `/contact`

**Request Body:**

```json
{
    "name": "string (required, max:50)",
    "email": "string (required, email, max:255)",
    "subject": "string (required, max:255)",
    "message": "string (required, max:500)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Query submitted successfully. We will get back to you soon.",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "subject": "Room Inquiry",
        "created_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

---

## 👤 User Endpoints

**Authentication Required** | **Role:** user

### User Bookings

#### Get User Bookings

**GET** `/user/bookings`

**Query Parameters:**

-   `limit` (optional, default: 10): Number of results per page
-   `page` (optional, default: 1): Page number
-   `status` (optional): Filter by booking status
-   `from_date` (optional): Filter bookings from this check-in date
-   `to_date` (optional): Filter bookings until this check-out date

**Response (200):**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "room_type_id": 1,
            "room_id": null,
            "status": "pending",
            "check_in_date": "2025-01-15T00:00:00.000000Z",
            "check_out_date": "2025-01-18T00:00:00.000000Z",
            "phone": "1234567890",
            "adult": 2,
            "children": 0,
            "total_price": 1500000,
            "is_paid": false,
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "room_type": {
                "id": 1,
                "name": "Deluxe Room",
                "images": []
            },
            "room": null
        }
    ],
    "pagination": {
        "total": 5,
        "current_page": 1,
        "limit": 10,
        "last_page": 1
    }
}
```

#### Create Booking

**POST** `/user/bookings`

**Request Body:**

```json
{
    "room_type_id": "integer (required, exists in room_types)",
    "check_in_date": "date (required, after_or_equal:today, YYYY-MM-DD)",
    "check_out_date": "date (required, after:check_in_date, YYYY-MM-DD)",
    "phone": "string (required, max:20)",
    "adult": "integer (required, min:1, max:10)",
    "children": "integer (required, min:0, max:10)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Booking created successfully",
    "data": {
        "booking": {
            "id": 1,
            "user_id": 1,
            "room_type_id": 1,
            "status": "pending",
            "check_in_date": "2025-01-15T00:00:00.000000Z",
            "check_out_date": "2025-01-18T00:00:00.000000Z",
            "total_price": 1500000,
            "room_type": {
                "name": "Deluxe Room",
                "price": 500000
            }
        },
        "nights": 3,
        "available_rooms": 5
    }
}
```

#### Get Booking by ID

**GET** `/user/bookings/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Booking retrieved successfully",
    "data": {
        "booking": {
            "id": 1,
            "status": "pending",
            "check_in_date": "2025-01-15T00:00:00.000000Z",
            "check_out_date": "2025-01-18T00:00:00.000000Z",
            "total_price": 1500000,
            "room_type": {},
            "room": null,
            "user": {}
        },
        "nights": 3,
        "days_until_checkin": 14,
        "can_cancel": true
    }
}
```

#### Cancel Booking

**POST** `/user/bookings/{id}/cancel`

**Response (200):**

```json
{
    "success": true,
    "message": "Booking cancelled successfully",
    "data": {
        "id": 1,
        "status": "cancelled"
    }
}
```

#### Get Booking Statistics

**GET** `/user/bookings/statistics`

**Response (200):**

```json
{
    "success": true,
    "message": "User booking statistics retrieved successfully",
    "data": {
        "total_bookings": 3,
        "pending_bookings": 1,
        "confirmed_bookings": 1,
        "completed_bookings": 1,
        "cancelled_bookings": 0,
        "total_spent": "2000000"
    }
}
```

---

## 🔧 Admin Endpoints

**Authentication Required** | **Role:** admin

### Admin Facilities

#### Get All Facilities (Admin)

**GET** `/admin/facilities`

**Query Parameters:**

-   `limit` (optional, default: 20): Number of results per page
-   `page` (optional, default: 1): Page number

**Response (200):**

```json
{
    "success": true,
    "message": "Facilities retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "wifi",
            "content": "Wi-Fi",
            "description": "Kết nối Internet tốc độ cao, miễn phí trong toàn bộ khách sạn, giúp bạn dễ dàng làm việc hoặc giải trí trực tuyến."
        },
        {
            "id": 2,
            "name": "conditioner",
            "content": "Máy Lạnh",
            "description": "Hệ thống điều hòa không khí hiện đại, mang lại không gian thoải mái và dễ chịu, phù hợp với mọi điều kiện thời tiết."
        }
    ],
    "pagination": {
        "total": 13,
        "current_page": 1,
        "limit": 5,
        "last_page": 3
    }
}
```

#### Create Facility

**POST** `/admin/facilities`

**Request Body:**

```json
{
    "name": "string (required, max:255)",
    "content": "string (required)",
    "description": "string (required)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Facility created successfully",
    "data": {
        "id": 14,
        "name": "test facility",
        "content": "Test Content",
        "description": "This is a test facility"
    }
}
```

#### Get Facility by ID (Admin)

**GET** `/admin/facilities/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Facility retrieved successfully",
    "data": {
        "id": 1,
        "name": "wifi",
        "content": "Wi-Fi",
        "description": "Kết nối Internet tốc độ cao, miễn phí trong toàn bộ khách sạn, giúp bạn dễ dàng làm việc hoặc giải trí trực tuyến."
    }
}
```

#### Update Facility

**PUT** `/admin/facilities/{id}`

**Request Body:**

```json
{
    "name": "string (optional, max:255)",
    "content": "string (optional)",
    "description": "string (optional)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Facility updated successfully",
    "data": {
        "id": 1,
        "name": "updated facility name",
        "content": "Updated Content",
        "description": "Updated description"
    }
}
```

#### Delete Facility

**DELETE** `/admin/facilities/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Facility deleted successfully"
}
```

### Admin Features

#### Get All Features (Admin)

**GET** `/admin/features`

**Query Parameters:**

-   `limit` (optional, default: 20): Number of results per page
-   `page` (optional, default: 1): Page number

**Response (200):**

```json
{
    "success": true,
    "message": "Features retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "bedroom",
            "content": "Phòng ngủ"
        },
        {
            "id": 2,
            "name": "balcony",
            "content": "Ban công"
        }
    ],
    "pagination": {
        "total": 10,
        "current_page": 1,
        "limit": 20,
        "last_page": 1
    }
}
```

#### Create Feature

**POST** `/admin/features`

**Request Body:**

```json
{
    "name": "string (required, max:255)",
    "content": "string (required)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Feature created successfully",
    "data": {
        "id": 11,
        "name": "new feature",
        "content": "New Feature Content"
    }
}
```

#### Get Feature by ID (Admin)

**GET** `/admin/features/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Feature retrieved successfully",
    "data": {
        "id": 1,
        "name": "bedroom",
        "content": "Phòng ngủ"
    }
}
```

#### Update Feature

**PUT** `/admin/features/{id}`

**Request Body:**

```json
{
    "name": "string (optional, max:255)",
    "content": "string (optional)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Feature updated successfully",
    "data": {
        "id": 1,
        "name": "updated feature name",
        "content": "Updated Feature Content"
    }
}
```

#### Delete Feature

**DELETE** `/admin/features/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Feature deleted successfully"
}
```

### Admin Room Types

#### Get All Room Types (Admin)

**GET** `/admin/room-types`

**Query Parameters:**

-   `limit` (optional, default: 20): Number of results per page
-   `page` (optional, default: 1): Page number
-   `search` (optional): Search by name or description

**Response (200):**

```json
{
    "success": true,
    "message": "Room types retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Phòng Deluxe",
            "area": 30,
            "price": 1000000,
            "quantity": 10,
            "adult": 2,
            "children": 0,
            "description": "Phòng Deluxe với diện tích 30m2",
            "created_at": "2024-11-29T00:00:00.000000Z",
            "updated_at": "2024-11-29T00:00:00.000000Z",
            "images": [],
            "facilities": [],
            "features": [],
            "rooms": []
        }
    ],
    "pagination": {
        "total": 4,
        "current_page": 1,
        "limit": 20,
        "last_page": 1
    }
}
```

#### Create Room Type

**POST** `/admin/room-types`

**Request Body:**

```json
{
    "name": "string (required, max:255, unique)",
    "area": "integer (required, min:1)",
    "price": "integer (required, min:0)",
    "quantity": "integer (required, min:0)",
    "adult": "integer (required, min:1, max:10)",
    "children": "integer (required, min:0, max:10)",
    "description": "string (required, max:500)",
    "facilities": "array (required, facility IDs)",
    "features": "array (required, feature IDs)",
    "images": "array (optional, image paths)",
    "thumbnail_image": "string (optional, thumbnail image path)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Room type created successfully",
    "data": {
        "id": 5,
        "name": "New Room Type",
        "area": 35,
        "price": 1200000,
        "quantity": 5,
        "adult": 2,
        "children": 1,
        "description": "New room type description",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "facilities": [
            {
                "id": 1,
                "name": "wifi",
                "content": "Wi-Fi"
            }
        ],
        "features": [
            {
                "id": 1,
                "name": "bedroom",
                "content": "Phòng ngủ"
            }
        ],
        "images": []
    }
}
```

#### Get Room Type by ID (Admin)

**GET** `/admin/room-types/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room type retrieved successfully",
    "data": {
        "id": 1,
        "name": "Phòng Deluxe",
        "area": 30,
        "price": 1000000,
        "quantity": 10,
        "adult": 2,
        "children": 0,
        "description": "Phòng Deluxe với diện tích 30m2",
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2024-11-29T00:00:00.000000Z",
        "images": [
            {
                "id": 1,
                "room_type_id": 1,
                "path": "storage/images/rooms/room-1.jpg",
                "is_thumbnail": true
            }
        ],
        "facilities": [
            {
                "id": 1,
                "name": "wifi",
                "content": "Wi-Fi",
                "pivot": { "room_type_id": 1, "facility_id": 1 }
            }
        ],
        "features": [
            {
                "id": 1,
                "name": "bedroom",
                "content": "Phòng ngủ",
                "pivot": { "room_type_id": 1, "feature_id": 1 }
            }
        ],
        "rooms": []
    }
}
```

#### Update Room Type

**PUT** `/admin/room-types/{id}`

**Request Body:**

```json
{
    "name": "string (optional, max:255, unique)",
    "area": "integer (optional, min:1)",
    "price": "integer (optional, min:0)",
    "quantity": "integer (optional, min:0)",
    "adult": "integer (optional, min:1, max:10)",
    "children": "integer (optional, min:0, max:10)",
    "description": "string (optional, max:500)",
    "facilities": "array (optional, facility IDs)",
    "features": "array (optional, feature IDs)",
    "images": "array (optional, image paths)",
    "thumbnail_image": "string (optional, thumbnail image path)",
    "replace_images": "boolean (optional, default: false)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Room type updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Room Type",
        "area": 35,
        "price": 1200000,
        "quantity": 8,
        "adult": 2,
        "children": 1,
        "description": "Updated room type description",
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "facilities": [],
        "features": [],
        "images": []
    }
}
```

#### Delete Room Type

**DELETE** `/admin/room-types/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room type deleted successfully"
}
```

### Admin Images

#### Get All Images

**GET** `/admin/images`

**Response (200):**

```json
{
    "success": true,
    "message": "Images retrieved successfully",
    "data": [
        {
            "path": "storage/images/rooms/room-1.jpg",
            "size": 245760,
            "last_modified": "2025-01-01T00:00:00.000000Z"
        },
        {
            "path": "storage/images/rooms/room-2.jpg",
            "size": 189432,
            "last_modified": "2025-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### Upload Image

**POST** `/admin/images/upload`

-   **Content-Type:** `multipart/form-data`

**Request Body:**

```
image: file (required, image, max:5MB)
```

**Response (201):**

```json
{
    "success": true,
    "message": "Image uploaded successfully",
    "data": {
        "path": "storage/images/rooms/1234567890_randomstring.jpg",
        "filename": "1234567890_randomstring.jpg",
        "original_name": "uploaded_image.jpg",
        "size": 245760,
        "mime_type": "image/jpeg"
    }
}
```

#### Delete Image

**DELETE** `/admin/images`

**Request Body:**

```json
{
    "path": "string (required, image path)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Image deleted successfully"
}
```

### Admin Room Images

#### Get All Room Images

**GET** `/admin/room-images`

**Query Parameters:**

-   `limit` (optional, default: 20): Number of results per page
-   `page` (optional, default: 1): Page number
-   `room_type_id` (optional): Filter by room type
-   `is_thumbnail` (optional): Filter by thumbnail status
-   `search` (optional): Search by image path

**Response (200):**

```json
{
    "success": true,
    "message": "Room images retrieved successfully",
    "data": [
        {
            "id": 1,
            "room_type_id": 1,
            "path": "storage/images/rooms/room-1.jpg",
            "is_thumbnail": true,
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "room_type": {
                "id": 1,
                "name": "Phòng Deluxe"
            }
        }
    ],
    "pagination": {
        "total": 20,
        "current_page": 1,
        "limit": 20,
        "last_page": 1
    }
}
```

#### Create Room Image

**POST** `/admin/room-images`

**Request Body:**

```json
{
    "room_type_id": "integer (required, exists in room_types)",
    "path": "string (required, max:255)",
    "is_thumbnail": "boolean (optional, default: false)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Room image created successfully",
    "data": {
        "id": 21,
        "room_type_id": 1,
        "path": "storage/images/rooms/new-image.jpg",
        "is_thumbnail": false,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe"
        }
    }
}
```

#### Get Room Images by Room Type

**GET** `/admin/room-images/room-type/{roomTypeId}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room type images retrieved successfully",
    "data": {
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe",
            "area": 30,
            "price": 1000000,
            "quantity": 10,
            "adult": 2,
            "children": 0,
            "description": "Phòng Deluxe với diện tích 30m2",
            "created_at": "2024-11-29T00:00:00.000000Z",
            "updated_at": "2024-11-29T00:00:00.000000Z"
        },
        "images": [
            {
                "id": 1,
                "room_type_id": 1,
                "path": "storage/images/rooms/room-1.jpg",
                "is_thumbnail": true,
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z",
                "url": "http://localhost:8000/storage/images/rooms/room-1.jpg"
            },
            {
                "id": 2,
                "room_type_id": 1,
                "path": "storage/images/rooms/room-1-2.jpg",
                "is_thumbnail": false,
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z",
                "url": "http://localhost:8000/storage/images/rooms/room-1-2.jpg"
            }
        ],
        "total_images": 2,
        "thumbnail_image": {
            "id": 1,
            "room_type_id": 1,
            "path": "storage/images/rooms/room-1.jpg",
            "is_thumbnail": true,
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "url": "http://localhost:8000/storage/images/rooms/room-1.jpg"
        }
    }
}
```

#### Get Room Image by ID

**GET** `/admin/room-images/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room image retrieved successfully",
    "data": {
        "id": 1,
        "room_type_id": 1,
        "path": "storage/images/rooms/room-1.jpg",
        "is_thumbnail": true,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe"
        }
    }
}
```

#### Update Room Image

**PUT** `/admin/room-images/{id}`

**Request Body:**

```json
{
    "room_type_id": "integer (optional, exists in room_types)",
    "path": "string (optional, max:255)",
    "is_thumbnail": "boolean (optional)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Room image updated successfully",
    "data": {
        "id": 1,
        "room_type_id": 1,
        "path": "storage/images/rooms/updated-image.jpg",
        "is_thumbnail": false,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

#### Delete Room Image

**DELETE** `/admin/room-images/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room image deleted successfully"
}
```

#### Set Thumbnail

**POST** `/admin/room-images/{id}/set-thumbnail`

**Response (200):**

```json
{
    "success": true,
    "message": "Thumbnail set successfully",
    "data": {
        "id": 1,
        "room_type_id": 1,
        "path": "storage/images/rooms/room-1.jpg",
        "is_thumbnail": true,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

### Admin Rooms

#### Get All Rooms

**GET** `/admin/rooms`

**Response (200):**

```json
{
    "success": true,
    "message": "Rooms retrieved successfully",
    "data": [
        {
            "id": 1,
            "room_type_id": 1,
            "room_number": "101",
            "is_active": true,
            "created_at": "2024-11-29T00:00:00.000000Z",
            "updated_at": "2024-11-29T00:00:00.000000Z",
            "room_type": {
                "id": 1,
                "name": "Phòng Deluxe",
                "area": 30,
                "price": 1000000,
                "quantity": 10,
                "adult": 2,
                "children": 0,
                "description": "Phòng Deluxe với diện tích 30m2, phù hợp cho gia đình hoặc nhóm bạn bè.",
                "created_at": "2024-11-29T00:00:00.000000Z",
                "updated_at": "2024-11-29T00:00:00.000000Z"
            }
        }
    ],
    "pagination": {
        "total": 20,
        "current_page": 1,
        "limit": 3,
        "last_page": 7
    }
}
```

#### Create Room

**POST** `/admin/rooms`

**Request Body:**

```json
{
    "room_type_id": "integer (required, exists in room_types)",
    "room_number": "string (required, unique)"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Room created successfully",
    "data": {
        "id": 21,
        "room_type_id": 1,
        "room_number": "A201",
        "is_active": true,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe"
        }
    }
}
```

#### Get Available Rooms

**GET** `/admin/rooms/available`

**Request Body:**

```json
{
    "room_type_id": "integer (required)",
    "check_in_date": "date (required, YYYY-MM-DD)",
    "check_out_date": "date (required, YYYY-MM-DD)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Available rooms retrieved successfully",
    "data": {
        "available_rooms": [
            {
                "id": 2,
                "room_type_id": 1,
                "room_number": "102",
                "is_active": true,
                "room_type": {
                    "id": 1,
                    "name": "Phòng Deluxe"
                }
            }
        ],
        "total_available": 5,
        "check_in_date": "2025-01-15",
        "check_out_date": "2025-01-18"
    }
}
```

#### Get Rooms by Room Type

**GET** `/admin/rooms/room-type/{roomTypeId}`

**Response (200):**

```json
{
    "success": true,
    "message": "Rooms retrieved successfully",
    "data": [
        {
            "id": 1,
            "room_type_id": 1,
            "room_number": "101",
            "is_active": true,
            "created_at": "2024-11-29T00:00:00.000000Z",
            "updated_at": "2024-11-29T00:00:00.000000Z"
        }
    ]
}
```

#### Get Room by ID

**GET** `/admin/rooms/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room retrieved successfully",
    "data": {
        "id": 1,
        "room_type_id": 1,
        "room_number": "101",
        "is_active": true,
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2024-11-29T00:00:00.000000Z",
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe",
            "area": 30,
            "price": 1000000
        }
    }
}
```

#### Update Room

**PUT** `/admin/rooms/{id}`

**Request Body:**

```json
{
    "room_number": "string (optional)",
    "is_active": "boolean (optional)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Room updated successfully",
    "data": {
        "id": 1,
        "room_type_id": 1,
        "room_number": "101A",
        "is_active": false,
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

#### Delete Room

**DELETE** `/admin/rooms/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Room deleted successfully"
}
```

### Admin Bookings

#### Get All Bookings

**GET** `/admin/bookings`

**Response (200):**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "room_type_id": 1,
            "room_id": null,
            "status": "pending",
            "check_in_date": "2024-11-29T00:00:00.000000Z",
            "check_out_date": "2024-12-01T00:00:00.000000Z",
            "phone": "0909090909",
            "adult": 2,
            "children": 0,
            "total_price": 1000000,
            "is_paid": false,
            "created_at": "2024-11-29T00:00:00.000000Z",
            "updated_at": "2024-11-29T00:00:00.000000Z",
            "user": {
                "id": 1,
                "name": "Thao",
                "email": "thao@gmail.com",
                "role": "user",
                "status": 1
            },
            "room_type": {
                "id": 1,
                "name": "Phòng Deluxe",
                "area": 30,
                "price": 1000000,
                "images": [
                    {
                        "id": 1,
                        "room_type_id": 1,
                        "path": "storage/images/rooms/room-1.jpg",
                        "is_thumbnail": true
                    }
                ]
            },
            "room": null
        }
    ],
    "pagination": {
        "total": 10,
        "current_page": 1,
        "limit": 20,
        "last_page": 1
    }
}
```

#### Get Booking by ID

**GET** `/admin/bookings/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Booking retrieved successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "room_type_id": 1,
        "room_id": null,
        "status": "pending",
        "check_in_date": "2024-11-29T00:00:00.000000Z",
        "check_out_date": "2024-12-01T00:00:00.000000Z",
        "phone": "0909090909",
        "adult": 2,
        "children": 0,
        "total_price": 1000000,
        "is_paid": false,
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2024-11-29T00:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Thao",
            "email": "thao@gmail.com"
        },
        "room_type": {
            "id": 1,
            "name": "Phòng Deluxe"
        },
        "room": null
    }
}
```

#### Update Booking

**PUT** `/admin/bookings/{id}`

**Request Body:**

```json
{
    "check_in_date": "date (optional, YYYY-MM-DD)",
    "check_out_date": "date (optional, YYYY-MM-DD, after:check_in_date)",
    "phone": "string (optional, max:20)",
    "adult": "integer (optional, min:1, max:10)",
    "children": "integer (optional, min:0, max:10)",
    "total_price": "numeric (optional, min:0)",
    "is_paid": "boolean (optional)",
    "status": "string (optional, values: pending|confirmed|checked-in|checked-out|completed|cancelled)",
    "room_id": "integer|null (optional, exists:rooms,id or null to remove room assignment)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Booking updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "room_type_id": 1,
        "room_id": null,
        "status": "confirmed",
        "check_in_date": "2024-11-29T00:00:00.000000Z",
        "check_out_date": "2024-12-01T00:00:00.000000Z",
        "phone": "0909090909",
        "adult": 2,
        "children": 0,
        "total_price": 1000000,
        "is_paid": false,
        "created_at": "2024-11-29T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

#### Delete Booking

**DELETE** `/admin/bookings/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Booking deleted successfully"
}
```

#### Get Available Rooms for Booking

**GET** `/admin/bookings/available-rooms`

**Query Parameters:**

-   `room_type_id` (required): Room type ID
-   `check_in_date` (required): Check-in date (YYYY-MM-DD)
-   `check_out_date` (required): Check-out date (YYYY-MM-DD)
-   `exclude_booking_id` (optional): Booking ID to exclude from availability check

**Response (200):**

```json
{
    "success": true,
    "message": "Available rooms retrieved successfully",
    "data": {
        "room_type": {
            "id": 1,
            "name": "Deluxe Room",
            "area": 30,
            "price": 1000000
        },
        "check_in_date": "2025-01-15",
        "check_out_date": "2025-01-18",
        "available_rooms": [
            {
                "id": 2,
                "room_type_id": 1,
                "room_number": "102",
                "is_active": true,
                "created_at": "2024-11-29T00:00:00.000000Z",
                "updated_at": "2024-11-29T00:00:00.000000Z",
                "room_type": {
                    "id": 1,
                    "name": "Deluxe Room",
                    "area": 30,
                    "price": 1000000
                }
            }
        ],
        "available_count": 5
    }
}
```

### Admin User Queries

#### Get All User Queries

**GET** `/admin/queries`

**Response (200):**

```json
{
    "success": true,
    "message": "Queries retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Hung",
            "email": "hung@gmail.com",
            "subject": "Tôi muốn đặt phòng",
            "message": "Cần hỗ trợ đặt phòng Tổng Thống.",
            "is_read": false,
            "created_at": "2024-11-29T00:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Trung",
            "email": "trung@gmail.com",
            "subject": "Yêu cầu hoàn tiền",
            "message": "Cần hỗ trợ hoàn tiền do huỷ đột xuất.",
            "is_read": true,
            "created_at": "2024-12-06T10:10:48.000000Z"
        }
    ],
    "statistics": {
        "total_queries": 2,
        "unread_queries": 1,
        "read_queries": 1,
        "today_queries": 0
    },
    "pagination": {
        "total": 2,
        "current_page": 1,
        "limit": 20,
        "last_page": 1
    }
}
```

#### Get User Query by ID

**GET** `/admin/queries/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Query retrieved successfully",
    "data": {
        "id": 1,
        "name": "Hung",
        "email": "hung@gmail.com",
        "subject": "Tôi muốn đặt phòng",
        "message": "Cần hỗ trợ đặt phòng Tổng Thống.",
        "is_read": false,
        "created_at": "2024-11-29T00:00:00.000000Z"
    }
}
```

#### Delete User Query

**DELETE** `/admin/queries/{id}`

**Response (200):**

```json
{
    "success": true,
    "message": "Query deleted successfully"
}
```

#### Delete Multiple User Queries

**DELETE** `/admin/queries`

**Request Body:**

```json
{
    "query_ids": "array (required, query IDs)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Queries deleted successfully",
    "data": {
        "deleted_count": 2,
        "deleted_ids": [1, 2]
    }
}
```

#### Update Read Status

**PUT** `/admin/queries/{id}/status`

**Request Body:**

```json
{
    "is_read": "boolean (required)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Query read status updated successfully",
    "data": {
        "id": 1,
        "name": "Hung",
        "email": "hung@gmail.com",
        "subject": "Tôi muốn đặt phòng",
        "message": "Cần hỗ trợ đặt phòng Tổng Thống.",
        "is_read": true,
        "created_at": "2024-11-29T00:00:00.000000Z"
    }
}
```

#### Update Multiple Read Status

**PUT** `/admin/queries/status`

**Request Body:**

```json
{
    "query_ids": "array (required, query IDs)",
    "is_read": "boolean (required)"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Query read statuses updated successfully",
    "data": {
        "updated_count": 2,
        "updated_ids": [1, 2],
        "is_read": false
    }
}
```

---

## 📊 Data Models

### User Model

```json
{
    "id": "integer",
    "name": "string",
    "email": "string",
    "address": "string|null",
    "phone": "string|null",
    "dob": "date|null",
    "avatar": "string|null",
    "role": "string (user|admin)",
    "status": "integer (0|1)",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Room Type Model

```json
{
    "id": "integer",
    "name": "string",
    "area": "integer",
    "price": "integer",
    "quantity": "integer",
    "adult": "integer",
    "children": "integer",
    "description": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "images": "array",
    "facilities": "array",
    "features": "array",
    "rooms": "array"
}
```

### Booking Order Model

```json
{
    "id": "integer",
    "user_id": "integer",
    "room_type_id": "integer",
    "room_id": "integer|null",
    "status": "string (pending|confirmed|checked-in|checked-out|completed|cancelled)",
    "check_in_date": "datetime",
    "check_out_date": "datetime",
    "phone": "string",
    "adult": "integer",
    "children": "integer",
    "total_price": "integer",
    "is_paid": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime",
    "user": "object",
    "room_type": "object",
    "room": "object|null"
}
```

### Room Model

```json
{
    "id": "integer",
    "room_type_id": "integer",
    "room_number": "string",
    "is_active": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime",
    "room_type": "object"
}
```

### Facility Model

```json
{
    "id": "integer",
    "name": "string",
    "content": "string",
    "description": "string"
}
```

### Feature Model

```json
{
    "id": "integer",
    "name": "string",
    "content": "string"
}
```

### Room Image Model

```json
{
    "id": "integer",
    "room_type_id": "integer",
    "path": "string",
    "is_thumbnail": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime",
    "room_type": "object"
}
```

### Query Model

```json
{
    "id": "integer",
    "name": "string",
    "email": "string",
    "subject": "string",
    "message": "string",
    "is_read": "boolean",
    "created_at": "datetime"
}
```

---

## 🔄 Standard Response Format

### Success Response

```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {} // Response data
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error description",
    "errors": {} // Validation errors (422 status)
}
```

### Pagination Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [],
    "pagination": {
        "total": 100,
        "current_page": 1,
        "limit": 20,
        "last_page": 5
    }
}
```

---

**For support or questions, please contact the development team.**
