# EduSocial - Educational Social Platform

A lightweight, secure, vanilla PHP and PDO-based social platform built for educational purposes. This project demonstrates core backend engineering concepts, relational database design, secure user authentication, and interactive content management without relying on heavy external frameworks.

---

## 🚀 Features

- **User Authentication & Security**:
  - Registration and login system using secure password hashing (`password_hash` with `PASSWORD_DEFAULT`).
  - Session protection against session fixation attacks (`session_regenerate_id`).
  - Prepared statements with PDO across all queries to prevent SQL Injection.
  - Output sanitization using `htmlspecialchars()` to prevent Cross-Site Scripting (XSS).
  - Post/Redirect/Get (PRG) pattern on form submissions to avoid duplicate data entry.

- **Post Management**:
  - Create short text posts (up to 500 characters).
  - Edit and update published post content (strictly authorized to original post authors).
  - Secure post deletion with cascading database cleanups.
  - Dedicated single-post permalink views (`post.php`).
  - Feed pagination using SQL `LIMIT` and `OFFSET`.

- **Interactivity & Engagement**:
  - Like and unlike posts dynamically.
  - Chronological comment threads attached to each post using batch lookups to avoid N+1 query performance issues.

- **User Profiles**:
  - Dedicated profile pages (`profile.php`) showing user details and authored post history.
  - Direct profile navigation from usernames across feeds and post discussions.

---

## 📂 Folder Structure

```text
edu-social/
│
├── config/
│   └── database.php      # PDO database connection settings
├── public/               # Web server document root
│   ├── css/
│   │   └── style.css     # General application styles
│   ├── index.php         # Main feed & pagination
│   ├── login.php         # User authentication login
│   ├── register.php      # New user registration
│   ├── logout.php       # Session termination
│   ├── post.php         # Single-post permalink view
│   ├── edit_post.php    # Post update interface
│   ├── delete_post.php  # Secure post deletion handler
│   ├── like.php         # Post like/unlike endpoint
│   ├── comment.php      # Comment submission endpoint
│   └── profile.php      # User profile view
└── schema.sql            # MySQL database schema setup script
```

---

## 🛠️ Requirements & Prerequisites

- **PHP**: version 7.4 or higher (PHP 8.x recommended) with the `pdo_mysql` extension enabled.
- **Database**: MySQL 5.7+ or MariaDB 10.2+.
- **Web Server**: Apache, Nginx, or PHP's built-in CLI development server.

---

## ⚙️ Database Setup

1. Log into your MySQL server via PHPMyAdmin, MySQL Workbench, or CLI.
2. Import or run the following `schema.sql` script to set up the database structure and relational foreign key constraints:

```sql
CREATE DATABASE IF NOT EXISTS edu_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edu_social;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts Table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    PRIMARY KEY (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🏁 Local Installation & Running Guide

1. **Clone/Copy the Repository**:
   Place the project files into your local web root directory (e.g., `htdocs` for XAMPP, `www` for WampServer, or any workspace directory).

2. **Configure Database Connection**:
   Open `config/database.php` and adjust the credentials according to your local environment:
   ```php
   $host = '127.0.0.1';
   $db   = 'edu_social';
   $user = 'root';        // Your database user
   $pass = '';            // Your database password
   ```

3. **Run the Application**:
   - **Option A (PHP Built-in Web Server)**:
     Open a terminal inside your project root and run:
     ```bash
     php -S localhost:8000 -t public
     ```
     Access the site in your browser at `http://localhost:8000`.

   - **Option B (XAMPP / MAMP / Apache)**:
     Point your virtual host or local web directory to the `public/` directory and navigate to `http://localhost/edu-social/public/register.php`.

---

## 🔒 Key Security Guidelines Covered

- **SQL Injection Prevention**: All queries use parameterized PDO statements or strict explicit type-binding (`PDO::PARAM_INT`).
- **XSS Prevention**: User input rendered in HTML templates is strictly escaped using `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
- **Authorization Verification**: Backend endpoints (`edit_post.php`, `delete_post.php`) independently re-verify post ownership before executing updates or deletions.