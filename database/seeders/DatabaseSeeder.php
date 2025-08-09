<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from havenly.sql
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data in correct order
        DB::table('booking_reviews')->delete();
        DB::table('booking_orders')->delete();
        DB::table('room_images')->delete();
        DB::table('room_features')->delete();
        DB::table('room_facilities')->delete();
        DB::table('rooms')->delete();
        DB::table('room_types')->delete();
        DB::table('features')->delete();
        DB::table('facilities')->delete();
        DB::table('queries')->delete();
        DB::table('users')->delete();

        // Seed Users (password: 123456)
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Hung', 'email' => 'hung@gmail.com', 'address' => '123 Nguyen Van Linh, Q9, TP.HCM', 'phone' => '0909090909', 'dob' => '1990-01-01', 'avatar' => 'chill-guy.png', 'password' => '$2y$10$6Mgw75KvzhFBqWR1nYjk1.FGcxz1JUV0qf42aNmrkxwxb35kHicUW', 'role' => 'user', 'status' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 2, 'name' => 'Trung', 'email' => 'trung@gmail.com', 'address' => '123 Nguyen Van Linh, Q9, TP.HCM', 'phone' => '0909090909', 'dob' => '1990-01-01', 'avatar' => 'chill-guy.png', 'password' => '$2y$10$6Mgw75KvzhFBqWR1nYjk1.FGcxz1JUV0qf42aNmrkxwxb35kHicUW', 'role' => 'user', 'status' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 3, 'name' => 'Huy', 'email' => 'huy@gmail.com', 'address' => '123 Nguyen Van Linh, Q9, TP.HCM', 'phone' => '0909090909', 'dob' => '1990-01-01', 'avatar' => 'chill-guy.png', 'password' => '$2y$10$6Mgw75KvzhFBqWR1nYjk1.FGcxz1JUV0qf42aNmrkxwxb35kHicUW', 'role' => 'user', 'status' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 4, 'name' => 'Hieu', 'email' => 'hieu@gmail.com', 'address' => '123 Nguyen Van Linh, Q9, TP.HCM', 'phone' => '0909090909', 'dob' => '1990-01-01', 'avatar' => 'chill-guy.png', 'password' => '$2y$10$6Mgw75KvzhFBqWR1nYjk1.FGcxz1JUV0qf42aNmrkxwxb35kHicUW', 'role' => 'user', 'status' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 5, 'name' => 'Admin', 'email' => 'admin@gmail.com', 'address' => '123 Nguyen Van Linh, Q9, TP.HCM', 'phone' => '0909090909', 'dob' => '1990-01-01', 'avatar' => 'chill-guy.png', 'password' => '$2y$10$6Mgw75KvzhFBqWR1nYjk1.FGcxz1JUV0qf42aNmrkxwxb35kHicUW', 'role' => 'admin', 'status' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
        ]);

        // Seed Facilities (exact data from SQL)
        DB::table('facilities')->insert([
            ['id' => 1, 'name' => 'wifi', 'content' => 'Wi-Fi', 'description' => 'Kết nối Internet tốc độ cao, miễn phí trong toàn bộ khách sạn, giúp bạn dễ dàng làm việc hoặc giải trí trực tuyến.'],
            ['id' => 2, 'name' => 'conditioner', 'content' => 'Máy Lạnh', 'description' => 'Hệ thống điều hòa không khí hiện đại, mang lại không gian thoải mái và dễ chịu, phù hợp với mọi điều kiện thời tiết.'],
            ['id' => 3, 'name' => 'tv', 'content' => 'Truyền Hình', 'description' => 'TV màn hình phẳng với đa dạng kênh giải trí trong nước và quốc tế, đáp ứng nhu cầu thư giãn của khách hàng.'],
            ['id' => 4, 'name' => 'desk', 'content' => 'Bàn làm việc', 'description' => 'Trang bị bàn làm việc tiện nghi, phù hợp cho cả công tác và nghỉ dưỡng.'],
            ['id' => 5, 'name' => 'heater', 'content' => 'Máy Sưởi', 'description' => 'Hệ thống sưởi ấm chất lượng cao, giữ không gian ấm áp, đặc biệt phù hợp vào những ngày lạnh giá.'],
            ['id' => 6, 'name' => 'water-heater', 'content' => 'Máy Nước Nóng', 'description' => 'Máy nước nóng tiện lợi, cung cấp nước nóng tức thì, đảm bảo sự thoải mái khi sử dụng phòng tắm.'],
            ['id' => 7, 'name' => 'safe', 'content' => 'Két sắt', 'description' => 'Két sắt điện tử giúp quý khách yên tâm cất giữ vật dụng có giá trị.'],
            ['id' => 8, 'name' => 'fridge', 'content' => 'Tủ lạnh', 'description' => 'Tủ lạnh mini tiện dụng, giữ lạnh thực phẩm và đồ uống.'],
            ['id' => 9, 'name' => 'kettle', 'content' => 'Bình siêu tốc', 'description' => 'Bình đun siêu tốc hỗ trợ pha trà, cà phê ngay trong phòng.'],
            ['id' => 10, 'name' => 'minibar', 'content' => 'Minibar', 'description' => 'Minibar với các loại thức uống và đồ ăn nhẹ được tuyển chọn.'],
            ['id' => 11, 'name' => 'shower', 'content' => 'Vòi sen', 'description' => 'Phòng tắm trang bị vòi sen hiện đại, mang lại cảm giác thư giãn.'],
            ['id' => 12, 'name' => 'wardrobe', 'content' => 'Tủ quần áo', 'description' => 'Tủ quần áo rộng rãi, có móc treo và không gian lưu trữ tiện lợi.'],
            ['id' => 13, 'name' => 'bathub', 'content' => 'Bồn tắm', 'description' => 'Thiết kế bồn tắm sang trọng, phù hợp cho những khoảnh khắc thư giãn riêng tư.'],
        ]);

        // Seed Features (exact data from SQL)
        DB::table('features')->insert([
            ['id' => 1, 'name' => 'bedroom', 'content' => 'Phòng ngủ'],
            ['id' => 2, 'name' => 'balcony', 'content' => 'Ban công'],
            ['id' => 3, 'name' => 'kitchen', 'content' => 'Nhà bếp'],
            ['id' => 4, 'name' => 'garden-view', 'content' => 'Hướng sân vườn'],
            ['id' => 5, 'name' => 'lake-view', 'content' => 'Hướng hồ'],
            ['id' => 6, 'name' => 'forest-view', 'content' => 'Hướng rừng thông'],
            ['id' => 7, 'name' => 'city-view', 'content' => 'Hướng thành phố'],
            ['id' => 8, 'name' => 'single-bed', 'content' => 'Giường đơn'],
            ['id' => 9, 'name' => 'double-bed', 'content' => 'Giường đôi'],
            ['id' => 10, 'name' => 'Interconnecting-room', 'content' => 'Phòng thông nhau'],
        ]);

        // Seed Room Types (exact data from SQL)
        DB::table('room_types')->insert([
            ['id' => 1, 'name' => 'Phòng Deluxe', 'area' => 30, 'price' => 1000000, 'quantity' => 10, 'adult' => 2, 'children' => 0, 'description' => 'Phòng Deluxe với diện tích 30m2, phù hợp cho gia đình hoặc nhóm bạn bè.', 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 2, 'name' => 'Phòng Premium', 'area' => 40, 'price' => 1500000, 'quantity' => 8, 'adult' => 2, 'children' => 0, 'description' => 'Phòng Premium với diện tích 40m2, phù hợp cho gia đình hoặc nhóm bạn bè.', 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 3, 'name' => 'Phòng Suite', 'area' => 50, 'price' => 2000000, 'quantity' => 6, 'adult' => 2, 'children' => 0, 'description' => 'Phòng Suite với diện tích 50m2, phù hợp cho gia đình hoặc nhóm bạn bè.', 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 4, 'name' => 'Phòng Presidential', 'area' => 100, 'price' => 5000000, 'quantity' => 2, 'adult' => 2, 'children' => 0, 'description' => 'Phòng Presidential với diện tích 100m2, phù hợp cho gia đình hoặc nhóm bạn bè.', 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
        ]);

        // Seed Rooms (exact data from SQL)
        DB::table('rooms')->insert([
            ['id' => 1, 'room_type_id' => 1, 'room_number' => '101', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 2, 'room_type_id' => 1, 'room_number' => '102', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 3, 'room_type_id' => 1, 'room_number' => '103', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 4, 'room_type_id' => 2, 'room_number' => '104', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 5, 'room_type_id' => 2, 'room_number' => '105', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 6, 'room_type_id' => 2, 'room_number' => '106', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 7, 'room_type_id' => 2, 'room_number' => '107', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 8, 'room_type_id' => 3, 'room_number' => '108', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 9, 'room_type_id' => 3, 'room_number' => '109', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 10, 'room_type_id' => 4, 'room_number' => '110', 'is_active' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
        ]);

        // Seed Room Facilities (exact data from SQL)
        DB::table('room_facilities')->insert([
            ['id' => 1, 'room_type_id' => 1, 'facility_id' => 1],
            ['id' => 2, 'room_type_id' => 1, 'facility_id' => 2],
            ['id' => 3, 'room_type_id' => 1, 'facility_id' => 3],
            ['id' => 4, 'room_type_id' => 1, 'facility_id' => 4],
            ['id' => 5, 'room_type_id' => 1, 'facility_id' => 5],
            ['id' => 6, 'room_type_id' => 1, 'facility_id' => 6],
            ['id' => 7, 'room_type_id' => 1, 'facility_id' => 7],
            ['id' => 8, 'room_type_id' => 1, 'facility_id' => 8],
            ['id' => 9, 'room_type_id' => 1, 'facility_id' => 9],
            ['id' => 10, 'room_type_id' => 1, 'facility_id' => 10],
        ]);

        // Seed Room Features (exact data from SQL)
        DB::table('room_features')->insert([
            ['id' => 1, 'room_type_id' => 1, 'feature_id' => 1],
            ['id' => 2, 'room_type_id' => 1, 'feature_id' => 2],
            ['id' => 3, 'room_type_id' => 1, 'feature_id' => 3],
            ['id' => 4, 'room_type_id' => 1, 'feature_id' => 4],
            ['id' => 5, 'room_type_id' => 1, 'feature_id' => 5],
            ['id' => 6, 'room_type_id' => 1, 'feature_id' => 6],
            ['id' => 7, 'room_type_id' => 1, 'feature_id' => 7],
            ['id' => 8, 'room_type_id' => 1, 'feature_id' => 8],
            ['id' => 9, 'room_type_id' => 1, 'feature_id' => 9],
            ['id' => 10, 'room_type_id' => 1, 'feature_id' => 10],
        ]);

        // Seed Room Images (exact data from SQL)
        DB::table('room_images')->insert([
            ['id' => 1, 'room_type_id' => 1, 'path' => 'room-1.jpg', 'is_thumbnail' => 1],
            ['id' => 2, 'room_type_id' => 1, 'path' => 'room-2.jpg', 'is_thumbnail' => 0],
            ['id' => 3, 'room_type_id' => 1, 'path' => 'room-3.jpg', 'is_thumbnail' => 0],
            ['id' => 4, 'room_type_id' => 2, 'path' => 'room-4.jpg', 'is_thumbnail' => 0],
            ['id' => 5, 'room_type_id' => 2, 'path' => 'room-5.jpg', 'is_thumbnail' => 0],
            ['id' => 6, 'room_type_id' => 2, 'path' => 'room-6.jpg', 'is_thumbnail' => 0],
            ['id' => 7, 'room_type_id' => 3, 'path' => 'room-7.jpg', 'is_thumbnail' => 1],
            ['id' => 8, 'room_type_id' => 3, 'path' => 'room-8.jpg', 'is_thumbnail' => 0],
            ['id' => 9, 'room_type_id' => 4, 'path' => 'room-9.jpg', 'is_thumbnail' => 1],
        ]);

        // Seed Booking Orders (exact data from SQL)
        DB::table('booking_orders')->insert([
            ['id' => 1, 'user_id' => 1, 'room_type_id' => 1, 'room_id' => null, 'status' => 'pending', 'check_in_date' => '2024-11-29 00:00:00', 'check_out_date' => '2024-12-01 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 2, 'user_id' => 1, 'room_type_id' => 2, 'room_id' => null, 'status' => 'confirmed', 'check_in_date' => '2024-12-01 00:00:00', 'check_out_date' => '2024-12-03 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 3, 'user_id' => 2, 'room_type_id' => 3, 'room_id' => 9, 'status' => 'checked-in', 'check_in_date' => '2024-12-03 00:00:00', 'check_out_date' => '2024-12-05 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 4, 'user_id' => 2, 'room_type_id' => 4, 'room_id' => 10, 'status' => 'completed', 'check_in_date' => '2024-12-05 00:00:00', 'check_out_date' => '2024-12-07 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 5, 'user_id' => 3, 'room_type_id' => 4, 'room_id' => 10, 'status' => 'cancelled', 'check_in_date' => '2024-12-07 00:00:00', 'check_out_date' => '2024-12-09 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 6, 'user_id' => 3, 'room_type_id' => 3, 'room_id' => 8, 'status' => 'pending', 'check_in_date' => '2024-12-09 00:00:00', 'check_out_date' => '2024-12-11 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 7, 'user_id' => 4, 'room_type_id' => 2, 'room_id' => 4, 'status' => 'confirmed', 'check_in_date' => '2024-12-11 00:00:00', 'check_out_date' => '2024-12-13 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 8, 'user_id' => 4, 'room_type_id' => 1, 'room_id' => 1, 'status' => 'checked-in', 'check_in_date' => '2024-12-13 00:00:00', 'check_out_date' => '2024-12-15 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 9, 'user_id' => 1, 'room_type_id' => 2, 'room_id' => null, 'status' => 'completed', 'check_in_date' => '2024-12-15 00:00:00', 'check_out_date' => '2024-12-17 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['id' => 10, 'user_id' => 2, 'room_type_id' => 1, 'room_id' => 2, 'status' => 'cancelled', 'check_in_date' => '2024-12-17 00:00:00', 'check_out_date' => '2024-12-19 00:00:00', 'phone' => '0909090909', 'adult' => 2, 'children' => 0, 'total_price' => 1000000, 'is_paid' => 0, 'is_reviewed' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
        ]);

        // Seed Booking Reviews (exact data from SQL)
        DB::table('booking_reviews')->insert([
            ['booking_order_id' => 1, 'rating' => 5, 'review' => 'Phòng rất đẹp, sạch sẽ, thoải mái.', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 2, 'rating' => 4, 'review' => '', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 3, 'rating' => 3, 'review' => 'Phòng rất đẹp, sạch sẽ, thoải mái.', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 4, 'rating' => 2, 'review' => 'Ảnh không giống phòng', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 5, 'rating' => 1, 'review' => 'Tôi không thích phòng này', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 6, 'rating' => 5, 'review' => 'ok', 'is_read' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 7, 'rating' => 4, 'review' => 'em la ai sao den noi day', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 8, 'rating' => 3, 'review' => 'abc', 'is_read' => 1, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 9, 'rating' => 2, 'review' => 'lorem ipsum dolor sit amet', 'is_read' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
            ['booking_order_id' => 10, 'rating' => 1, 'review' => 'lorem ipsum', 'is_read' => 0, 'created_at' => '2024-11-29 00:00:00', 'updated_at' => '2024-11-29 00:00:00'],
        ]);

        // Seed Queries (exact data from SQL)
        DB::table('queries')->insert([
            ['id' => 1, 'name' => 'Hung', 'email' => 'hung@gmail.com', 'subject' => 'Tôi muốn đặt phòng', 'message' => 'Cần hỗ trợ đặt phòng Tổng Thống.', 'is_read' => 0, 'created_at' => '2024-11-29 00:00:00'],
            ['id' => 2, 'name' => 'Trung', 'email' => 'trung@gmail.com', 'subject' => 'Yêu cầu hoàn tiền', 'message' => 'Cần hỗ trợ hoàn tiền do huỷ đột xuất.', 'is_read' => 1, 'created_at' => '2024-12-06 10:10:48'],
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
