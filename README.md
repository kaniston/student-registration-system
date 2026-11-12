# Student Registration System

A web-based Student Registration System built with PHP, MySQL, and MongoDB. It allows administrators to manage student records, track changes, and monitor registration activity through an analytics dashboard.

## Features

- **Admin Authentication** – Secure login and logout.
- **Student Management** – Add, view, edit, and delete student records.
- **Search** – Find students by name, email, or course.
- **Input Validation** – Validate student details and prevent duplicate email addresses.
- **MySQL Database** – Store student information.
- **MongoDB Activity Logging** – Record student registration, update, and deletion activities.
- **Activity Logs** – View recent administrative activities.
- **Dashboard Analytics** – View student totals, course distribution, and activity summaries.
- **Security** – Prepared SQL statements, password hashing, session checks, and CSRF protection for protected actions.

## Tech Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Relational Database: MySQL
- NoSQL Database: MongoDB
- MongoDB PHP Library: Composer
- Charts: Chart.js
- Local Server: XAMPP
- Development Tools: Visual Studio Code, MongoDB Compass

## Project Structure

```text
student-registration-system/
│
├── index.php
├── login.php
├── logout.php
├── auth.php
├── db.php
├── edit_student.php
├── delete_student.php
├── log_activity.php
├── activity_logs.php
├── analytics.php
├── composer.json
├── composer.lock
├── .gitignore
└── README.md
```

## Local Installation

### 1. Requirements

- XAMPP with Apache, MySQL, and PHP
- MongoDB Community Server
- Composer
- PHP MongoDB extension

### 2. Clone the Repository

```bash
git clone https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
```

Move the project folder into your XAMPP `htdocs` directory.

### 3. Create the MySQL Database

Open phpMyAdmin and create a database named:

`student_registration`

Run this SQL:

```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4. Configure Database Connection

Update `db.php` with your local MySQL credentials.

### 5. Install PHP Dependencies

Open a terminal in the project directory and run:

```bash
composer install
```

### 6. Configure MongoDB

Start MongoDB and ensure the PHP MongoDB extension is enabled.

The application uses the following MongoDB database and collection:

- Database: `student_activity`
- Collection: `logs`

### 7. Create an Admin Account

Provision an administrator account using a secure setup process that stores the password with PHP `password_hash()`. Do not commit setup scripts or real credentials to GitHub.

### 8. Run the Application

Start Apache and MySQL in XAMPP and open:

`http://localhost/student-registration-system/`

Log in with your administrator account.

## Database Architecture

**MySQL – Student Records**

Stores student details such as name, email, course, age, and registration date.

**MongoDB – Activity Logs**

Stores activity records such as:

- `student_registered`
- `student_updated`
- `student_deleted`

This demonstrates the use of both relational and NoSQL databases in one application.

## Future Improvements

- Export student records to CSV or PDF.
- Add pagination for large student lists.
- Add role-based access control.
- Improve analytics and reporting.
- Add automated testing.

## Author

**Kaniston S.**  
B.Sc. Computer Science Student

GitHub: https://github.com/kaniston
