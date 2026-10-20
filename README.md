# Simple Social Platform (Vanilla PHP & PDO)

A lightweight, secure social media platform built from scratch using **Vanilla PHP** and **PDO** for educational purposes. It covers core backend fundamentals including secure database design, authentication, session management, and CRUD operations for posts and comments.

Lazily generated with Gemini Auto Low ([https://share.gemini.google/v9IZHTDNx6ZB](https://share.gemini.google/v9IZHTDNx6ZB)).

---

## Features

* **User Authentication:** Secure registration, password hashing (`password_hash`), session fixation protection, and login/logout mechanics.
* **Feed & Pagination:** Chronological global feed split into manageable pages using SQL `LIMIT` and `OFFSET`.
* **Post Management:** Create, view (global feed or single-post permalinks), edit, and delete posts with strict backend author authorization.
* **Engagement:** Like/unlike posts dynamically and add threaded comments.
* **User Profiles:** View individual user profile pages and their published post history.
* **Security Practices:** Prepared statements against SQL injection, XSS prevention via `htmlspecialchars()`, and the Post/Redirect/Get (PRG) pattern.

---

## Project Structure

```text
php_pdo_social_platform/
│
├── database.php        # PDO database connection setup
├── index.php           # Main feed stream with pagination
├── register.php        # User account registration
├── login.php           # User login and session initialization
├── logout.php          # Session destruction handler
├── create_post.php     # Form and logic for creating new posts
├── post.php            # Single post permalink view & comment thread
├── edit_post.php       # Post editing interface and handler
├── delete_post.php     # Secure post deletion handler
├── profile.php         # Dedicated user profile page
├── like.php            # Like/unlike toggle action handler
├── comment.php         # Comment submission handler
├── style.css           # Global stylesheet
├── schema.sql          # Complete database schema backup
└── README.md           # Project documentation