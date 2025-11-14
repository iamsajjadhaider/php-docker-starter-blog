-- Create the main posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL, -- Links to an authors/users table (simplified below)
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT TRUE
);

-- Create a simple users table for the admin (or authors)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'author'
);

-- Populate necessary initial data (the admin user and a sample post)

-- Using a reliable BCRYPT hash for 'securepassword123'
-- Use this hash for testing: $2y$10$tUj3w3vA0gLwL6pE8n5yC.i.j.8O0n.r.a.r.P
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$f7gxTQZ4R7XrEGKKm0RMVet1veEX5NGrNFLNKJqRWN.zzLQFCrZwO', 'admin'); 

INSERT INTO posts (title, content, author_id) VALUES
('Welcome to the Blog', 'This is the first post created to show the public view. We are using Tailwind CSS for a modern, responsive design. Go to Admin Login and use `admin` and `securepassword123` to manage posts!', 1),
('Understanding CRUD Operations', 'Create, Read, Update, and Delete are the foundational elements of persistent data storage. This application demonstrates all four.', 1);
