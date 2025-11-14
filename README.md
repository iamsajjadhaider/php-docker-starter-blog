🐳 PHP/MySQL Docker Starter Blog

This project is a simple, fully containerized blogging platform designed as a perfect starting point for learning how to use Docker and Docker Compose to run a complete web application.

It features a classic PHP (plain) / MySQL stack styled with modern Tailwind CSS.

✨ Features

Dockerized Stack: The entire application runs in two isolated containers: one for the PHP/Web Server (app) and one for the MySQL Database (db).

Persistent Data: Uses a Docker Named Volume to guarantee that all your blog posts are saved and available even after stopping and restarting your containers.

Admin Panel (CRUD): Includes basic Create, Read, Update, and Delete (CRUD) functionality for managing posts.

Minimal Authentication: Features a single admin user (admin) for post management.

Modern Styling: A clean, responsive design built with Tailwind CSS.

🚀 Getting Started (Run in 3 Steps)

Prerequisites

You only need Docker Desktop (or Docker Engine and Docker Compose) installed on your machine.

1. Clone the Repository & Setup the .env

First, clone the project and navigate into the directory:

git clone YOUR_REPO_URL_HERE
cd php-docker-starter-blog


Next, create the secret configuration file. Create a new file named .env in the root directory and paste the following content.

Note: You must change the values below for production, but you can use these defaults for testing.

# --- Database Configuration (Used by the PHP application) ---
MYSQL_DATABASE=blog_db_name
MYSQL_USER=blog_user
MYSQL_PASSWORD=securepassword

# --- Root Credentials (Used by Docker to set up MySQL) ---
MYSQL_ROOT_PASSWORD=veryrootpassword


2. Build and Run the Stack

This command will automatically:

Build the custom PHP image.

Start both the app and db containers.

Execute schema.sql to create the tables and initial admin user.

Create the persistent volume for MySQL data.

docker-compose up -d


3. Access the Application

The web application will be accessible via your browser on port 8080.

http://localhost:8080

🔑 Admin Login Credentials

The application is seeded with a default admin user.

Field

Value

Username

admin

Password

securepassword123

Use the Admin Panel link on the homepage to log in and start managing your posts.

💡 Essential Docker Commands for Beginners

These are the most common commands you'll use while working with this project:

Command

Purpose

docker-compose up -d

START the stack (or rebuild if you changed code/config).

docker-compose down

STOP and REMOVE containers and networks, but keeps your data volume (mysql_data). Use this most often!

docker-compose logs -f app

Streams the real-time logs for the PHP web application.

docker-compose down -v

STOP and REMOVE EVERYTHING including the persistent data volume. Use this only when you need a completely clean reset.
