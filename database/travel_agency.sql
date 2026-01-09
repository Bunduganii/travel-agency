-- Travel Agency Booking and Reservation System Database
-- Created for Final Year Project

CREATE DATABASE IF NOT EXISTS travel_agency_db;
USE travel_agency_db;

-- Users table (for both admin and customers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flights table
CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(20) NOT NULL,
    airline VARCHAR(100) NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_date DATETIME NOT NULL,
    arrival_date DATETIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available_seats INT DEFAULT 0,
    aircraft VARCHAR(50),
    stops INT DEFAULT 0,
    duration VARCHAR(20),
    class_type VARCHAR(20) DEFAULT 'Economy',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hotels table
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    star_rating INT DEFAULT 3,
    price_per_night DECIMAL(10, 2) NOT NULL,
    amenities TEXT,
    description TEXT,
    available_rooms INT DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tour packages table
CREATE TABLE IF NOT EXISTS tour_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    destination VARCHAR(200) NOT NULL,
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2),
    description TEXT,
    inclusions TEXT,
    image_url VARCHAR(255),
    package_type VARCHAR(50),
    rating DECIMAL(3, 1) DEFAULT 0,
    available_spots INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table (for flights)
CREATE TABLE IF NOT EXISTS flight_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    passengers INT DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE
);

-- Hotel reservations table
CREATE TABLE IF NOT EXISTS hotel_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    rooms INT DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Tour package bookings table
CREATE TABLE IF NOT EXISTS tour_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    travel_date DATE NOT NULL,
    travelers INT DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES tour_packages(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_type ENUM('flight', 'hotel', 'tour') NOT NULL,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
-- Default Login Credentials:
-- Email: admin@travelagency.com
-- Username: admin
-- Password: password
-- ⚠️ IMPORTANT: Change this password after first login for security!
-- Note: Password must be hashed using PHP password_hash() function
INSERT INTO users (username, email, password, full_name, user_type) VALUES
('admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');

-- Insert sample flights
INSERT INTO flights (flight_number, airline, origin, destination, departure_date, arrival_date, price, available_seats, aircraft, stops, duration, class_type) VALUES
('DL452', 'Delta', 'JFK', 'LHR', '2024-10-24 08:00:00', '2024-10-24 14:55:00', 580.00, 50, 'Boeing 777', 0, '6h 55m', 'Economy'),
('BA117', 'British Airways', 'JFK', 'LHR', '2024-10-24 10:30:00', '2024-10-24 18:45:00', 520.00, 30, 'Airbus A350', 1, '8h 15m', 'Economy'),
('VS045', 'Virgin Atlantic', 'JFK', 'LHR', '2024-10-24 13:20:00', '2024-10-24 20:25:00', 615.00, 25, 'Airbus A330', 0, '7h 05m', 'Premium');

-- Insert sample hotels
INSERT INTO hotels (name, location, city, country, star_rating, price_per_night, amenities, description, available_rooms, image_url) VALUES
('Grand Skyline Tokyo', 'Shinjuku Ward', 'Tokyo', 'Japan', 5, 185.00, 'Free WiFi,Breakfast Included,Pool', 'Luxurious hotel in the heart of Tokyo', 20, 'hotel1.jpg'),
('Shibuya Crossing Inn', 'Shibuya', 'Tokyo', 'Japan', 4, 145.00, 'Free WiFi,Gym', 'Modern hotel near Shibuya crossing', 15, 'hotel2.jpg'),
('Asakusa Ryokan Heritage', 'Asakusa', 'Tokyo', 'Japan', 5, 260.00, 'Free WiFi,Onsen/Spa,Breakfast Included', 'Traditional Japanese ryokan experience', 10, 'hotel3.jpg');

-- Insert sample tour packages
INSERT INTO tour_packages (title, destination, duration_days, duration_nights, price, original_price, description, inclusions, image_url, package_type, rating, available_spots) VALUES
('Santorini Sunset Getaway', 'Santorini, Greece', 5, 4, 1499.00, 1800.00, 'Experience the magical sunsets of Santorini', '5 Days/4 Nights,Flight Included,4-Star Hotel,Breakfast', 'santorini.jpg', 'Romantic', 4.9, 15),
('Kyoto Cultural Immersion', 'Kyoto, Japan', 7, 6, 2100.00, 2400.00, 'Discover traditional Japanese culture', '7 Days/6 Nights,Rail Pass,Ryokan Stay,Guide', 'kyoto.jpg', 'Cultural', 4.8, 12),
('Cappadocia Dream', 'Cappadocia, Turkey', 4, 3, 999.00, 1200.00, 'Hot air balloon adventure over unique landscapes', '4 Days/3 Nights,Flight Included,Cave Hotel,Photo Shoot', 'cappadocia.jpg', 'Adventure', 5.0, 20);

