
CREATE DATABASE IF NOT EXISTS cesnc_example;

USE cesnc_example;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20),
    date_of_birth DATE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS message_recipients (
    message_id INT NOT NULL,
    recipient_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id, recipient_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
);
-- $2y$12$vib6kTkNk23dXRq7ATP8suKHzbkstN1u48.W5aOB3xyyappphv5Y.   -> This is a hashed password for '123'
-- $2y$12$aSTfZH7VPuIzLF6c2IHVTeTW6F.S4OV/Vq.lV96Ufz0k5VGyYbxrG   -> This is a hashed password for 'admin'
INSERT INTO users (first_name,last_name,address,phone,date_of_birth,email,password,role,is_active)
VALUES
('Admin', 'Admin', '123 Main St', '936182912', '1990-01-01', 'admin@cesnc.com', '$2y$12$aSTfZH7VPuIzLF6c2IHVTeTW6F.S4OV/Vq.lV96Ufz0k5VGyYbxrG', 'admin', TRUE),
('Joao', 'Smith', '456 Elm St', '927182391', '1985-05-15', 'joao@cesnc.com', '$2y$12$vib6kTkNk23dXRq7ATP8suKHzbkstN1u48.W5aOB3xyyappphv5Y.', 'user', TRUE);