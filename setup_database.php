<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS shop_db";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->exec("USE shop_db");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `password` VARCHAR(100) NOT NULL,
        `user_type` ENUM('user', 'admin', 'seller') NOT NULL DEFAULT 'user',
        `company_name` VARCHAR(100),
        `image` VARCHAR(100),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Users table created successfully<br>";
    
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS `products` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `image` VARCHAR(100) NOT NULL,
        `product_detail` TEXT NOT NULL,
        `seller_id` INT NOT NULL,
        `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        PRIMARY KEY (`id`),
        FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Products table created successfully<br>";
    
    // Create cart table
    $sql = "CREATE TABLE IF NOT EXISTS `cart` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `pid` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `quantity` INT NOT NULL,
        `image` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`pid`) REFERENCES `products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Cart table created successfully<br>";
    
    // Create wishlist table
    $sql = "CREATE TABLE IF NOT EXISTS `wishlist` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `pid` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `image` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`pid`) REFERENCES `products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Wishlist table created successfully<br>";
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `number` VARCHAR(12) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `address` VARCHAR(500) NOT NULL,
        `address_type` VARCHAR(20) NOT NULL,
        `method` VARCHAR(50) NOT NULL,
        `product_id` INT NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `qty` INT NOT NULL,
        `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
        `status_updated_at` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Orders table created successfully<br>";
    
    // Create message table
    $sql = "CREATE TABLE IF NOT EXISTS `message` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `number` VARCHAR(12) NOT NULL,
        `message` VARCHAR(500) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Message table created successfully<br>";
    
    // Create addresses table
    $sql = "CREATE TABLE IF NOT EXISTS `addresses` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `address` VARCHAR(500) NOT NULL,
        `address_type` VARCHAR(20) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Addresses table created successfully<br>";
    
    // Create reviews table
    $sql = "CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `seller_id` INT NOT NULL,
        `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
        `review_text` VARCHAR(1000),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_review` (`user_id`, `product_id`, `seller_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Reviews table created successfully<br>";
    
    echo "All tables created successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?> 