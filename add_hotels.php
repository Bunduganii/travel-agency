<?php
/**
 * Add Sample Hotels to Database
 * Run this once to populate hotels
 */
require_once 'includes/db.php';

$hotels = [
    // Hargeisa Hotels
    ['name' => 'Hargeisa Grand Hotel', 'location' => 'Airport Road', 'city' => 'Hargeisa', 'country' => 'Somalia', 'star_rating' => 5, 'price_per_night' => 120.00, 'amenities' => 'Free WiFi,Breakfast Included,Pool,Gym,Spa', 'description' => 'Luxurious hotel in the heart of Hargeisa with modern amenities', 'available_rooms' => 25],
    ['name' => 'Hargeisa Plaza Hotel', 'location' => 'City Center', 'city' => 'Hargeisa', 'country' => 'Somalia', 'star_rating' => 4, 'price_per_night' => 85.00, 'amenities' => 'Free WiFi,Breakfast Included,Gym', 'description' => 'Comfortable hotel in the city center', 'available_rooms' => 30],
    ['name' => 'Somali Heritage Inn', 'location' => 'Main Street', 'city' => 'Hargeisa', 'country' => 'Somalia', 'star_rating' => 3, 'price_per_night' => 55.00, 'amenities' => 'Free WiFi', 'description' => 'Traditional Somali hospitality', 'available_rooms' => 20],
    
    // Tokyo Hotels
    ['name' => 'Grand Skyline Tokyo', 'location' => 'Shinjuku Ward', 'city' => 'Tokyo', 'country' => 'Japan', 'star_rating' => 5, 'price_per_night' => 185.00, 'amenities' => 'Free WiFi,Breakfast Included,Pool', 'description' => 'Luxurious hotel in the heart of Tokyo', 'available_rooms' => 20],
    ['name' => 'Shibuya Crossing Inn', 'location' => 'Shibuya', 'city' => 'Tokyo', 'country' => 'Japan', 'star_rating' => 4, 'price_per_night' => 145.00, 'amenities' => 'Free WiFi,Gym', 'description' => 'Modern hotel near Shibuya crossing', 'available_rooms' => 15],
    ['name' => 'Asakusa Ryokan Heritage', 'location' => 'Asakusa', 'city' => 'Tokyo', 'country' => 'Japan', 'star_rating' => 5, 'price_per_night' => 260.00, 'amenities' => 'Free WiFi,Onsen/Spa,Breakfast Included', 'description' => 'Traditional Japanese ryokan experience', 'available_rooms' => 10],
    
    // More diverse locations
    ['name' => 'Dubai Marina Resort', 'location' => 'Marina Walk', 'city' => 'Dubai', 'country' => 'UAE', 'star_rating' => 5, 'price_per_night' => 220.00, 'amenities' => 'Free WiFi,Breakfast Included,Pool,Gym,Spa', 'description' => '5-star resort overlooking the marina', 'available_rooms' => 40],
    ['name' => 'Istanbul Bosphorus Hotel', 'location' => 'Bosphorus Shore', 'city' => 'Istanbul', 'country' => 'Turkey', 'star_rating' => 4, 'price_per_night' => 95.00, 'amenities' => 'Free WiFi,Breakfast Included,Gym', 'description' => 'Historic hotel with Bosphorus views', 'available_rooms' => 28],
    ['name' => 'Cairo Nile View Hotel', 'location' => 'Nile Corniche', 'city' => 'Cairo', 'country' => 'Egypt', 'star_rating' => 4, 'price_per_night' => 75.00, 'amenities' => 'Free WiFi,Breakfast Included,Pool', 'description' => 'Hotel with stunning Nile views', 'available_rooms' => 35],
    ['name' => 'London Westminster Hotel', 'location' => 'Westminster', 'city' => 'London', 'country' => 'UK', 'star_rating' => 5, 'price_per_night' => 195.00, 'amenities' => 'Free WiFi,Breakfast Included,Gym,Spa', 'description' => 'Elegant hotel near Big Ben', 'available_rooms' => 22],
];

$added = 0;
$skipped = 0;

foreach ($hotels as $hotel) {
    // Check if hotel already exists
    $stmt = $conn->prepare("SELECT id FROM hotels WHERE name = ? AND city = ?");
    $stmt->bind_param("ss", $hotel['name'], $hotel['city']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert hotel
        $insert_stmt = $conn->prepare("INSERT INTO hotels (name, location, city, country, star_rating, price_per_night, amenities, description, available_rooms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssidssi", 
            $hotel['name'],
            $hotel['location'],
            $hotel['city'],
            $hotel['country'],
            $hotel['star_rating'],
            $hotel['price_per_night'],
            $hotel['amenities'],
            $hotel['description'],
            $hotel['available_rooms']
        );
        
        if ($insert_stmt->execute()) {
            $added++;
        }
        $insert_stmt->close();
    } else {
        $skipped++;
    }
    $stmt->close();
}

echo "<h1>Hotels Added Successfully!</h1>";
echo "<p>Added: $added hotels</p>";
echo "<p>Skipped (already exist): $skipped hotels</p>";
echo "<p><a href='customer/reserve_hotel.php'>Go to Hotel Search</a></p>";
?>

