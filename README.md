# EduSocial Platform (Vanilla PHP & PDO)

A simple, secure, and fully functional social networking platform built from scratch using vanilla PHP and PDO. Designed for educational purposes to master backend fundamentals, secure database interactions, and state management.

Lazily generated with Gemini Auto Low ([https://share.gemini.google/6FqjgsOTPPv9](https://share.gemini.google/6FqjgsOTPPv9)).

---

## 🚀 Features

* **User Authentication:** Secure registration with `password_hash()`, login verification with `password_verify()`, and session fixation protection.
* **Post Management:** 
  * Create posts via a dedicated form (`create_post.php`).
  * Read a paginated global feed (`index.php`).
  * View deep comment threads on a permalink page (`post.php`).
  * Edit and delete posts with strict backend ownership verification.
* **Engagement:** Like/unlike posts dynamically and add chronological comments.
* **User Profiles:** Dedicated profile pages (`profile.php?id=...`) showing user details and authored posts.
* **Security Practices:** Protection against SQL injection (PDO prepared statements), XSS (`htmlspecialchars()`), and CSRF/PRG patterns.

---

## 📂 Project Structure (Flat Directory)

```text
php_pdo_social_platform/
├── database.php        # Secure PDO database connection
├── schema.sql          # MySQL database schema backup
├── index.php           # Main paginated feed & interactions
├── register.php        # New user registration
├── login.php           # User login and session initialization
├── logout.php          # Session cleanup and termination
├── create_post.php     # Dedicated post creation form
├── post.php            # Single-post permalink view with comments
├── edit_post.php       # Post editing interface and logic
├── delete_post.php     # Backend authorization and post deletion
├── like.php            # Like / unlike toggle endpoint
├── comment.php         # Comment submission handler
├── profile.php         # User profile and post history view
└── style.css           # Global application stylesheet
```

---

## 🛠️ Setup & Installation Instructions

### Prerequisites
Make sure you have a local PHP development environment installed, such as **XAMPP**, **WAMP**, or **MAMP** (which includes PHP, Apache, and MySQL).

### Step 1: Place Files in Your Web Root
Move your project directory (`php_pdo_social_platform`) into your local server's web root folder:
* **XAMPP (Windows):** `C:\xampp\htdocs\`
* **WAMP (Windows):** `C:\wamp64\www\`
* **MAMP (Mac):** `/Applications/MAMP/htdocs/`

### Step 2: Set Up the Database
1. Start **Apache** and **MySQL** from your XAMPP/WAMP/MAMP control panel.
2. Open your browser and go to `http://localhost/phpmyadmin/` (or open your preferred MySQL client).
3. Create a new database named `php_pdo_social`.
4. Run the SQL script from your `schema.sql` file to create the required tables (`users`, `posts`, `likes`, `comments`). You can do this by clicking on the `php_pdo_social` database, navigating to the **SQL** tab, pasting the contents of `schema.sql`, and clicking **Go**.

### Step 3: Configure Database Connection
Open `database.php` in your code editor and verify that your database credentials match:

```php
<?php
$host = '127.0.0.1';
$db   = 'php_pdo_social';
$user = 'root';
$pass = ''; // Enter your MySQL password here if you have one set
$charset = 'utf8mb4';
```

### Step 4: Run the Application
1. Open your browser and navigate to the project directory:
   `http://localhost/php_pdo_social_platform/login.php`
2. Click **Register** to create a new account.
3. Once logged in, click **Create New Post**, publish your thoughts, and explore the feed, comments, likes, and profile pages!

---

## 🛡️ Security Highlights
* **Prepared Statements:** All database inputs use PDO parameters to completely block SQL injection.
* **Data Sanitization:** User-generated content is escaped using `htmlspecialchars()` before rendering to prevent Cross-Site Scripting (XSS).
* **Authorization Checks:** Actions like editing or deleting posts verify that the active session user ID matches the post's owner ID in the database.