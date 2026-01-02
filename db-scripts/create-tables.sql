-- Create DB

CREATE DATABASE blog;
USE blog;

-- Create tables in DB
-- Schema from https://mysql101.com/mysql-tutorial/2025/01/09/Designing-a-Set-of-Tables-for-User-Login-in/
-- Changes to schema: postfix foreign keys with FK_ to make it more clear

CREATE TABLE users (
   user_id INT AUTO_INCREMENT PRIMARY KEY,
   username VARCHAR(50) NOT NULL UNIQUE,
   password_hash VARCHAR(255) NOT NULL,
   email VARCHAR(100) UNIQUE NOT NULL,
   role ENUM('blogger','admin') NOT NULL DEFAULT 'blogger',
   is_active BOOLEAN DEFAULT TRUE,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- user_image
CREATE TABLE user_image (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    FK_user_id INT NOT NULL UNIQUE,
    file_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (FK_user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE login_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,  -- Unique log ID
    FK_user_id INT NOT NULL,                   -- User ID (foreign key)
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Time of login
    ip_address VARCHAR(45),                 -- IP address (supports IPv4 and IPv6)
    device_info VARCHAR(255),               -- Information about the device used
    success BOOLEAN DEFAULT TRUE,           -- Whether the login was successful
    FOREIGN KEY (FK_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description VARCHAR(255)
);

-- articles
CREATE TABLE articles (
      article_id INT AUTO_INCREMENT PRIMARY KEY,
      FK_user_id INT NOT NULL,
      FK_category_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      content LONGTEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      is_active BOOLEAN DEFAULT TRUE,

      FULLTEXT KEY ft_title_content (title, content),

      FOREIGN KEY (FK_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
      FOREIGN KEY (FK_category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- article_images
CREATE TABLE article_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    FK_article_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (FK_article_id) REFERENCES articles(article_id) ON DELETE CASCADE
);



-- Practical statements

-- Add new user
INSERT INTO users (username, password_hash, email, role)
VALUES ('lexan', SHA2('lexan', 256), 'lexan@gmail.com', 'admin');

-- Add new user
INSERT INTO users (username, password_hash, email)
VALUES ('alice', SHA2('test123', 256), 'alice@example.com');

-- Add new user
INSERT INTO users (username, password_hash, email)
VALUES ('bob', SHA2('test123', 256), 'bob@example.com');

-- Add new login attempt
INSERT INTO login_logs (FK_user_id, ip_address, device_info, success)
VALUES (1, '192.168.1.1', 'Chrome on Windows 10', TRUE);

-- Get login history for user
SELECT login_time, ip_address, device_info, success
FROM login_logs
WHERE FK_user_id = 1
ORDER BY login_time DESC;

-- Example Categories
INSERT INTO categories (name, description)
VALUES
    ('Technology', 'Tech news and tutorials'),
    ('Lifestyle', 'Life hacks, habits, and more'),
    ('Travel', 'Travel guides and tips'),
    ('Health', 'Health & wellness articles');

-- Example Articles
INSERT INTO articles (FK_user_id, FK_category_id, title, content)
VALUES
    (1,
     2,
     'How to Build a Website in 2025',
     'A beginner-friendly guide to building modern websites.'
    );

-- Exmaple Articles
INSERT INTO articles (FK_user_id, FK_category_id, title, content)
VALUES
    (2,
     2,
     'Top 10 Places to Visit in Europe',
     'A curated list of must-see destinations in Europe.'
    );

-- Add picture to article
INSERT INTO article_images (FK_article_id, file_path, alt_text)
VALUES
    (1, '/picture-uploads/articles/hero.jpg', 'Laptop on a desk'),
    (1, '/picture-uploads/articles/diagram.png', 'Website architecture diagram'),
    (2, '/picture-uploads/articles/beach.jpg', 'Sunny beach in Spain');

-- Fulltext Search

-- Word "Website":
SELECT article_id, title
FROM articles
WHERE MATCH(title, content)
    AGAINST ('website' IN NATURAL LANGUAGE MODE)
  AND is_active = 1;

-- Word "Europe":
SELECT article_id, title
FROM articles
WHERE MATCH(title, content)
    AGAINST ('europe' IN NATURAL LANGUAGE MODE)
  AND is_active = 1;

-- Get Article + Picture:
SELECT a.*, i.file_path, i.alt_text
FROM articles a
         LEFT JOIN article_images i ON a.article_id = i.FK_article_id
WHERE a.article_id = 1;


-- Get Article by Category
SELECT a.title, c.name AS category
FROM articles a
         JOIN categories c ON c.category_id = a.FK_category_id
WHERE a.article_id = 1;

-- Get every Article from User:
SELECT article_id, title, created_at
FROM articles
WHERE FK_user_id = 1
ORDER BY created_at DESC;

-- Get every Article from Category
SELECT article_id, title
FROM articles
WHERE FK_category_id = 3;