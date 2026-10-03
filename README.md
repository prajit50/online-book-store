# BookNest — Online Book Store Management System

A small-to-medium BCA 4th Semester Project Work application built with HTML5, CSS3, vanilla JavaScript, core PHP, and MySQL. It lets customers browse and search books, manage a cart, and place Cash on Delivery orders. An administrator can manage books, categories, customers, and order statuses.

## Requirements

- XAMPP with Apache and MySQL running
- PHP 7.4+ (PHP 8+ recommended)
- A modern web browser

## Installation with XAMPP

1. Copy the `online-book-store` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Select the **Import** tab, choose `database/bookstore.sql`, then click **Import**.
5. Open `http://localhost/online-book-store/` in a browser.

The default database connection is configured in `includes/db.php`:

```php
host: localhost
username: root
password: (empty)
database: bookstore_db
```

Update it if your local XAMPP credentials differ.

## Default administrator

| Field | Value |
|---|---|
| Email | `admin@booknest.test` |
| Password | `admin123` |

Change this password or replace the seeded account before deployment.

## Main features

- Secure registration and login with `password_hash()` / `password_verify()`
- Search by title, author, and category
- Responsive catalog and detailed book pages
- Session-based cart with quantity updates and stock limits
- COD checkout, stock reduction, and order history
- Admin dashboard with book, category, order, and user management
- Prepared statements for all user-provided database values
- Safe image type/size checks for book covers

## Project structure

```text
online-book-store/
├── admin/              Administrator pages
├── css/style.css        Responsive custom styling
├── database/bookstore.sql
├── diagrams/            Mermaid source for academic diagrams
├── images/              Placeholder assets
├── includes/            Shared connection, auth, header/footer files
├── javascript/script.js Menu and small form interactions
├── tests/test-cases.md
├── uploads/             Uploaded book covers (must be writable)
└── index.php            Store front
```

## Notes for evaluation

- Payment is intentionally restricted to Cash on Delivery; no third-party API is used.
- Make sure `uploads/` is writable by Apache if image uploads fail.
- The SQL file resets tables during import, so do not import it over important data.
- Diagram files use Mermaid syntax and can be viewed in GitHub, VS Code Mermaid extensions, or Mermaid Live Editor.

## Suggested demonstration sequence

1. Login as administrator and add a category/book.
2. Register a regular customer account.
3. Search for a book, add it to the cart, and check out with COD.
4. Show the order in **My Orders**.
5. Return to the admin dashboard and update the order status.
