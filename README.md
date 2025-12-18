# 🌍 GriotShelf
# GriotShelf - Digital Library Manager

GriotShelf is a simple web application designed to help users manage their books. You can keep track of what you are reading, organize books into custom shelves, and follow other readers.

This project was built using PHP for the backend and standard HTML/CSS/JavaScript for the frontend.

## Key Features

1.  User Accounts
    *   Sign up and log in securely.
    *   Set up a personal profile.
    *   Manage privacy settings for your shelves.

2.  Book Tracking
    *   Browse a collection of books.
    *   Add books to your Reading List (Want to Read, Currently Reading, Finished).
    *   Create custom "Shelves" (like 'Favorites' or 'School Books') to organize your collection.

3.  Social Features
    *   Search for other users by their username.
    *   Follow other readers to see their public shelves.
    *   Leave reviews and ratings for books you've read.

## How to Set Up

To run this project locally, you will need a local server environment like XAMPP or MAMP.

1.  Database Setup
    *   Open phpMyAdmin.
    *   Create a new database named `griotshelf`.
    *   Import the `database/schema.sql` file to create the necessary tables.

2.  Configuration
    *   Check `config/database.php` to make sure the database connection settings (host, username, password) match your local setup.

3.  Running the App
    *   Move the `GriotShelf` folder into your `htdocs` directory.
    *   Open your browser and go to `http://localhost/GriotShelf`.

## Built With
*   PHP (Server-side logic)
*   MySQL (Database)
*   Bootstrap 5 (Styling)
*   JavaScript & Fetch API (Asynchronous updates)

Created for a University Project.

### 👤 Standard User (Demo)
*   **Email**: `kwame@example.com`
*   **Password**: `Password123`
---

## 🛠️ Setup Instructions

1.  **Environment**: Ensure you have XAMPP (or MAMP/LAMP) installed with Apache and MySQL running.
2.  **Files**: Place the `GriotShelf` folder in your `htdocs` directory.
3.  **Database**:
    *   Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
    *   Create a clean database named `griotshelf` (or drop the existing one).
    *   Import the `database/schema.sql` file. This will create all tables and insert the seed data.
4.  **Launch**: Open your browser and navigate to `http://localhost/GriotShelf`.

## 📂 Project Structure

*   `api/`: Backend PHP handlers for logic and AJAX requests.
*   `config/`: Database connection settings.
*   `css/`: Custom styling (Variables, Dark accents, Layouts).
*   `database/`: SQL schema and seed data.
*   `includes/`: Reusable fragments (Navbar, Footer).
*   `pages/`: Frontend views (Books, Profile, Admin, etc.).
*   `index.php`: Landing page.

---
*© 2025 GriotShelf Project*
