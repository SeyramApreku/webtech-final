-- GriotShelf Complete Database Schema (Railway Version)
-- Modified for Railway MySQL database

-- Drop existing tables if they exist
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS follows;
DROP TABLE IF EXISTS shelf_books;
DROP TABLE IF EXISTS shelves;
DROP TABLE IF EXISTS reading_list;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- 1. USERS TABLE
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    privacy_want_to_read TINYINT(1) DEFAULT 1,
    privacy_currently_reading TINYINT(1) DEFAULT 1,
    privacy_finished TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. BOOKS TABLE
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    cover_url VARCHAR(500),
    publication_year INT,
    genre VARCHAR(50),
    region VARCHAR(50),
    language VARCHAR(50),
    isbn VARCHAR(20),
    page_count INT,
    publisher VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. REVIEWS TABLE
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    contains_spoilers TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- 4. READING LIST TABLE
CREATE TABLE reading_list (
    list_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('want_to_read', 'currently_reading', 'finished') DEFAULT 'want_to_read',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_book (user_id, book_id)
);

-- 5. SHELVES TABLE
CREATE TABLE shelves (
    shelf_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shelf_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_public TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. SHELF_BOOKS TABLE
CREATE TABLE shelf_books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shelf_id INT NOT NULL,
    book_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shelf_id) REFERENCES shelves(shelf_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_shelf_book (shelf_id, book_id)
);

-- 7. FOLLOWS TABLE
CREATE TABLE follows (
    follow_id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    CHECK (follower_id != following_id)
);

-- 8. CONTACT MESSAGES TABLE
CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

