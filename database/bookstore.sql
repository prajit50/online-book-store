-- BookNest Online Book Store Management System
-- Import this file in phpMyAdmin after creating/opening MySQL.
CREATE DATABASE IF NOT EXISTS bookstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstore_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE books (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  author VARCHAR(120) NOT NULL,
  isbn VARCHAR(30) NULL UNIQUE,
  publisher VARCHAR(120) NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 0,
  description TEXT NULL,
  image VARCHAR(255) NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  UNIQUE KEY unique_cart_book (cart_id, book_id),
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  province VARCHAR(100) NULL,
  district VARCHAR(100) NULL,
  municipality VARCHAR(100) NULL,
  ward VARCHAR(20) NULL,
  street_address TEXT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(20) NOT NULL DEFAULT 'COD',
  status ENUM('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Default administrator password: admin123 (change after first login)
INSERT INTO users (full_name,email,phone,password,role) VALUES
('BookNest Administrator','admin@booknest.test','9800000000','$2y$10$K0VmjR7Y/F/spY05T8oIpel1T.zWRflZTLul5lfjnUk9yxDOgM.Ny','admin');

INSERT INTO categories (name,description) VALUES
('Programming','Software development and coding books'),
('Database','Database design and management'),
('Networking','Computer networks and security'),
('AI','Artificial intelligence and machine learning'),
('Fiction','Stories, novels, and literature'),
('Science','Popular science and discovery');

INSERT INTO books (category_id,title,author,isbn,publisher,price,quantity,description,featured) VALUES
(1,'Learning PHP and MySQL','Robin Nixon','9781491978917','O''Reilly Media',1250.00,14,'A practical introduction to building dynamic database-driven websites with PHP and MySQL.',1),
(1,'JavaScript: The Definitive Guide','David Flanagan','9781491952023','O''Reilly Media',1850.00,8,'A comprehensive guide to the JavaScript language and browser APIs.',1),
(2,'Database System Concepts','Abraham Silberschatz','9780078022159','McGraw-Hill',2100.00,10,'Fundamental concepts of database systems, SQL, normalization, and transactions.',1),
(3,'Computer Networking','James F. Kurose','9780133594140','Pearson',1900.00,6,'A top-down approach to understanding modern computer networks.',0),
(4,'Artificial Intelligence: A Modern Approach','Stuart Russell','9780134610993','Pearson',2600.00,5,'The classic textbook covering principles and practice of artificial intelligence.',1),
(5,'The Alchemist','Paulo Coelho','9780061122415','HarperOne',650.00,20,'A timeless novel about pursuing dreams and discovering one''s personal legend.',0),
(6,'A Brief History of Time','Stephen Hawking','9780553380163','Bantam',780.00,12,'An accessible exploration of the universe, space, and time.',0);
