CREATE DATABASE eazypay;

USE eazypay;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    passwd VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- total_expenses DECIMAL(10, 2) DEFAULT 0.00 -- (optional)
);

-- Communities Table
CREATE TABLE communities (
    community_id INT AUTO_INCREMENT PRIMARY KEY,
    community_name VARCHAR(100) NOT NULL,
    community_description VARCHAR(255) NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin INT,
    coin VARCHAR(10) DEFAULT 'EUR',
    FOREIGN KEY (admin) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Community Members Table
CREATE TABLE community_members (
    user_id INT,
    community_id INT,
    -- join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- (optional)
    -- status ENUM('active', 'inactive') DEFAULT 'active', -- (optional)
    accumulated_balance DECIMAL(10, 2) DEFAULT 0.00,
    PRIMARY KEY (user_id, community_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES communities(community_id) ON DELETE CASCADE
);

-- Expenses Table
CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    community_id INT,
    expense_description TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payer INT,
    FOREIGN KEY (community_id) REFERENCES communities(community_id) ON DELETE CASCADE,
    FOREIGN KEY (payer) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Expense Participants Table
CREATE TABLE expense_participants (
    expense_id INT,
    user_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    ratio_percentage FLOAT DEFAULT 1.0,
    PRIMARY KEY (expense_id, user_id),
    FOREIGN KEY (expense_id) REFERENCES expenses(expense_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Debts Table (debt relationships between users)
CREATE TABLE debts (
    debt_id INT AUTO_INCREMENT PRIMARY KEY,
    debtor INT,
    creditor INT,
    community_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    FOREIGN KEY (debtor) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (creditor) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES communities(community_id) ON DELETE CASCADE
);

-- Sample Data

INSERT INTO users (username, email, passwd)
VALUES
    ('Juan Perez', 'juan.perez@example.com', 'password123'),
    ('Maria Garcia', 'maria.garcia@example.com', 'password123'),
    ('Pedro Martinez', 'pedro.martinez@example.com', 'password123'),
    ('Laura Lopez', 'laura.lopez@example.com', 'password123');

INSERT INTO communities (community_name, community_description, admin)
VALUES
    ('Friends Community', 'Community for sharing leisure expenses', 1),
    ('Family', 'Family community for events and shared expenses', 2),
    ('Work Colleagues', 'Shared expenses for work activities', 3);

INSERT INTO community_members (user_id, community_id)
VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (2, 2),
    (1, 3),
    (3, 3),
    (4, 3);

INSERT INTO expenses (community_id, expense_description, total_amount, payer)
VALUES
    (1, 'Group Dinner', 50.00, 1),
    (1, 'Beer', 20.00, 2),
    (2, 'Birthday Gift', 100.00, 3),
    (3, 'Work Lunch', 30.00, 1);

INSERT INTO expense_participants (expense_id, user_id, amount, ratio_percentage)
VALUES
    (1, 1, 25.00, 0.5),
    (1, 2, 25.00, 0.5),
    (2, 1, 20.00, 1.0),
    (3, 3, 100.00, 1.0),
    (4, 1, 30.00, 1.0);

INSERT INTO debts (debtor, creditor, community_id, amount, status)
VALUES
    (2, 1, 1, 25.00, 'pending'),
    (3, 1, 1, 25.00, 'pending'),
    (2, 3, 2, 100.00, 'pending');

-- Create a new user
CREATE USER 'eazypay'@'localhost' IDENTIFIED BY 'eazypaypebb';

-- Grant privileges to the user on the eazypay database
GRANT ALL PRIVILEGES ON eazypay.* TO 'eazypay'@'localhost' WITH GRANT OPTION;

-- Apply the changes
FLUSH PRIVILEGES;
