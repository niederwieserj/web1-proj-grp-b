
-------------------------------------------------
-- Create DB

CREATE DATABASE blog;
USE blog;

-------------------------------------------------
-- Create tables in DB
-- Schema from https://mysql101.com/mysql-tutorial/2025/01/09/Designing-a-Set-of-Tables-for-User-Login-in/
-- Changes to schema: postfix foreign keys with FK_ to make it more clear

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique user ID
    username VARCHAR(50) NOT NULL UNIQUE,   -- Username
    password_hash VARCHAR(255) NOT NULL,    -- Encrypted password
    email VARCHAR(100) UNIQUE,              -- Email address
    phone VARCHAR(15),                      -- Phone number (optional)
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

CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique role ID
    role_name VARCHAR(50) UNIQUE NOT NULL,  -- Role name (e.g., Admin, User)
    description VARCHAR(255)                -- Description of the role
);

CREATE TABLE user_roles (
    FK_user_id INT NOT NULL,       -- User ID
    FK_role_id INT NOT NULL,       -- Role ID
    PRIMARY KEY (FK_user_id, FK_role_id),
    FOREIGN KEY (FK_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (FK_role_id) REFERENCES roles(role_id) ON DELETE CASCADE
);

CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique permission ID
    permission_name VARCHAR(100) UNIQUE NOT NULL, -- Permission name
    description VARCHAR(255)                      -- Description of the permission
);

CREATE TABLE role_permissions (
    FK_role_id INT NOT NULL,          -- Role ID
    FK_permission_id INT NOT NULL,    -- Permission ID
    PRIMARY KEY (FK_role_id, FK_permission_id),
    FOREIGN KEY (FK_role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (FK_permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
);

-------------------------------------------------
-- Insert statements

INSERT INTO users (username, password_hash, email, phone)
VALUES ('john_doe', 'hashed_password_here', 'john@example.com', '1234567890');

INSERT INTO login_logs (user_id, ip_address, device_info, success)
VALUES (1, '192.168.1.1', 'Chrome on Windows 10', TRUE);

SELECT login_time, ip_address, device_info, success
FROM login_logs
WHERE user_id = 1
ORDER BY login_time DESC;

INSERT INTO user_roles (user_id, role_id)
VALUES (1, 2); -- Assign role_id 2 to user_id 1

SELECT p.permission_name
FROM permissions p
JOIN role_permissions rp ON p.permission_id = rp.permission_id
JOIN user_roles ur ON rp.role_id = ur.role_id
WHERE ur.user_id = 1;
