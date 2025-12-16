-- Create DB

CREATE DATABASE blog;
USE blog;

-- Create tables in DB
-- Schema from https://mysql101.com/mysql-tutorial/2025/01/09/Designing-a-Set-of-Tables-for-User-Login-in/
-- Changes to schema: postfix foreign keys with FK_ to make it more clear

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique user ID
    username VARCHAR(50) NOT NULL UNIQUE,   -- Username
    password_hash VARCHAR(255) NOT NULL,    -- Encrypted password
    email VARCHAR(100) UNIQUE,              -- Email address
    is_active BOOLEAN DEFAULT TRUE,         -- Account activation status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Account creation time
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Last updated time
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

-- ------------------------------
-- Table Roles to save every Role
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique role ID
    role_name VARCHAR(50) UNIQUE NOT NULL,  -- Role name (e.g., Admin, User)
    description VARCHAR(255)                -- Description of the role
);

-- Since one user can have multiple roles and otherwise, an intermediate table is needed
CREATE TABLE user_roles (
    FK_user_id INT NOT NULL,       -- User ID
    FK_role_id INT NOT NULL,       -- Role ID
    PRIMARY KEY (FK_user_id, FK_role_id),
    FOREIGN KEY (FK_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (FK_role_id) REFERENCES roles(role_id) ON DELETE CASCADE
);
-- ------------------------------

-- ------------------------------
-- What is allowed e. g. view_articles
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique permission ID
    permission_name VARCHAR(100) UNIQUE NOT NULL, -- Permission name
    description VARCHAR(255)                      -- Description of the permission
);

-- Which role does have which permission
CREATE TABLE role_permissions (
    FK_role_id INT NOT NULL,          -- Role ID
    FK_permission_id INT NOT NULL,    -- Permission ID
    PRIMARY KEY (FK_role_id, FK_permission_id),
    FOREIGN KEY (FK_role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (FK_permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
);
-- ------------------------------

-- articles
CREATE TABLE articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    FK_user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    summary TEXT,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT TRUE,

    FULLTEXT KEY ft_title_summary (title, summary),
    FULLTEXT KEY ft_content (content),

    FOREIGN KEY (FK_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description VARCHAR(255)
);

-- N:M Zuordnungstabelle article_categories
CREATE TABLE article_categories (
    FK_article_id INT NOT NULL,
    FK_category_id INT NOT NULL,
    PRIMARY KEY (FK_article_id, FK_category_id),
    FOREIGN KEY (FK_article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
    FOREIGN KEY (FK_category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

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
INSERT INTO users (username, password_hash, email)
VALUES ('john_doe', 'test', 'john@example.com');

-- Add new user
INSERT INTO users (username, password_hash, email)
VALUES ('bob', 'hashed_password_here', 'bob@example.com');

-- Add new login attempt
INSERT INTO login_logs (FK_user_id, ip_address, device_info, success)
VALUES (1, '192.168.1.1', 'Chrome on Windows 10', TRUE);

-- Get login history for user
SELECT login_time, ip_address, device_info, success
FROM login_logs
WHERE FK_user_id = 1
ORDER BY login_time DESC;


-- user_roles
INSERT INTO `user_roles` (`FK_user_id`, `FK_role_id`) VALUES ('1', '2');
INSERT INTO `user_roles` (`FK_user_id`, `FK_role_id`) VALUES ('1', '3');
INSERT INTO `user_roles` (`FK_user_id`, `FK_role_id`) VALUES ('2', '2');

-- Roles
INSERT INTO roles (role_name, description) VALUES
('user',    'Can only read'),
('blogger', 'Can create and edit own articles'),
('admin',   'Full system access');

-- ------------------------------
-- Permissions
INSERT INTO permissions (permission_name, description) VALUES
('view_articles',      'Read articles'),
('create_article',     'Create articles'),
('edit_own_article',   'Edit own articles'),
('delete_any_article', 'Delete any article'),
('manage_users',       'Manage users');

-- Roles --> Permissions
-- user  --> only read
INSERT INTO role_permissions VALUES (1, 1);

-- blogger --> read, write blog, edit own blog
INSERT INTO role_permissions VALUES (2, 1), (2, 2), (2, 3);

-- admin --> full rights
INSERT INTO role_permissions VALUES (3, 1), (3, 2), (3, 3), (3, 4), (3, 5);
-- ------------------------------

-- Example Categories
INSERT INTO categories (name, slug, description)
VALUES
    ('Technology', 'technology', 'Tech news and tutorials'),
    ('Lifestyle', 'lifestyle', 'Life hacks, habits, and more'),
    ('Travel', 'travel', 'Travel guides and tips'),
    ('Health', 'health', 'Health & wellness articles');

-- Exmaple Articles (Alice)
INSERT INTO articles (FK_user_id, title, slug, summary, content)
VALUES
    (1,
     'How to Build a Website in 2025',
     'build-website-2025',
     'A beginner-friendly guide to building modern websites.',
     'Full article content goes here ... including HTML, text, and formatting.'
    );

-- Exmaple Articles (Bob)
INSERT INTO articles (FK_user_id, title, slug, summary, content)
VALUES
    (2,
     'Top 10 Places to Visit in Europe',
     'top-10-places-europe',
     'A curated list of must-see destinations in Europe.',
     'Lots of travel content here ...'
    );


-- Link Categories to Articles

-- Article 1 --> Technology + Lifestyle
INSERT INTO article_categories (FK_article_id, FK_category_id)
VALUES
    (1, 1),
    (1, 2);

-- Article 1 --> Travel
INSERT INTO article_categories (FK_article_id, FK_category_id)
VALUES
    (2, 3);


-- Add picture to article
INSERT INTO article_images (FK_article_id, file_path, alt_text)
VALUES
    (1, '/picture-uploads/articles/hero.jpg', 'Laptop on a desk'),
    (1, '/picture-uploads/articles/diagram.png', 'Website architecture diagram'),
    (2, '/picture-uploads/articles/beach.jpg', 'Sunny beach in Spain');

-- Fulltexct Search

-- Word "Website":
SELECT article_id, title, summary
FROM articles
WHERE MATCH(title, summary) AGAINST ('website' IN NATURAL LANGUAGE MODE);

-- Word "Europe":
SELECT article_id, title, summary
FROM articles
WHERE MATCH(title, summary) AGAINST ('Europe' IN NATURAL LANGUAGE MODE);

-- Get Article + Picture:
SELECT a.*, i.file_path, i.alt_text
FROM articles a
         LEFT JOIN article_images i ON a.article_id = i.FK_article_id
WHERE a.slug = 'build-website-2025';


-- Get Article by Category:
SELECT a.title, c.name AS category
FROM articles a
         JOIN article_categories ac ON ac.FK_article_id = a.article_id
         JOIN categories c ON c.category_id = ac.FK_category_id
WHERE a.article_id = 1;

-- Get every Article from User:
SELECT article_id, title, created_at
FROM articles
WHERE FK_user_id = 1
ORDER BY created_at DESC;

-- Get every Article from Category:
SELECT a.article_id, a.title
FROM articles a
         JOIN article_categories ac ON ac.FK_article_id = a.article_id
         JOIN categories c ON c.category_id = ac.FK_category_id
WHERE c.slug = 'travel';