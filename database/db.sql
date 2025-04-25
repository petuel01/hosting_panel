CREATE DATABASE IF NOT EXISTS lxd_hosting;
USE lxd_hosting;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, 
    linux_username VARCHAR(50) UNIQUE DEFAULT NULL,
    allocated_space INT DEFAULT NULL -- Space in KB
);

CREATE TABLE wordpress_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain VARCHAR(255) NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    db_name VARCHAR(255) NOT NULL,
    db_user VARCHAR(255) NOT NULL,
    db_password VARCHAR(255) NOT NULL,
    wp_username VARCHAR(255) NOT NULL,
    wp_password VARCHAR(255) NOT NULL,
    wp_email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);