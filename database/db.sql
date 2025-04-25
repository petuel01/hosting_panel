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