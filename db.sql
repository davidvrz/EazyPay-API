CREATE DATABASE eazypay;

USE eazypay;

-- Users Table
CREATE TABLE users (
    username VARCHAR(100) PRIMARY KEY,
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
    admin VARCHAR(100) NOT NULL,
    coin VARCHAR(10) DEFAULT 'EUR',
    FOREIGN KEY (admin) REFERENCES users(username) ON DELETE CASCADE
);

-- Community Members Table
CREATE TABLE community_members (
    member VARCHAR(100) NOT NULL,
    community INT NOT NULL,
    -- join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- (optional)
    -- status ENUM('active', 'inactive') DEFAULT 'active', -- (optional)
    accumulated_balance DECIMAL(10, 2) DEFAULT 0.00,
    PRIMARY KEY (member, community),
    FOREIGN KEY (member) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (community) REFERENCES communities(community_id) ON DELETE CASCADE
);

-- Expenses Table
CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    community INT NOT NULL,
    expense_description TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    date TIMESTAMP DEFAULTS CURRENT_TIMESTAMP,
    payer VARCHAR(100) NOT NULL,
    FOREIGN KEY (community) REFERENCES communities(community_id) ON DELETE CASCADE,
    FOREIGN KEY (payer) REFERENCES users(username) ON DELETE CASCADE
);

-- Expense Participants Table
CREATE TABLE expense_participants (
    expense INT,
    member VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    ratio_percentage FLOAT DEFAULT 1.0,
    PRIMARY KEY (expense, member),
    FOREIGN KEY (expense) REFERENCES expenses(expense_id) ON DELETE CASCADE,
    FOREIGN KEY (member) REFERENCES users(username) ON DELETE CASCADE
);

-- Debts Table (debt relationships between users)
CREATE TABLE debts (
    debt_id INT AUTO_INCREMENT PRIMARY KEY,
    debtor VARCHAR(100) NOT NULL,
    creditor VARCHAR(100) NOT NULL,
    community INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    FOREIGN KEY (debtor) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (creditor) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (community) REFERENCES communities(community_id) ON DELETE CASCADE
);

-- Sample Data

INSERT INTO users (username, email, passwd)
VALUES
    ('JuanPerez', 'juanperez@example.com', 'password123'),
    ('MariaGarcia', 'mariagarcia@example.com', 'password123'),
    ('PedroMartinez', 'pedromartinez@example.com', 'password123'),
    ('LauraLopez', 'lauralopez@example.com', 'password123');

INSERT INTO communities (community_name, community_description, admin)
VALUES
    ('Friends Community', 'Community for sharing leisure expenses', 'JuanPerez'),
    ('Family', 'Family community for events and shared expenses', 'MariaGarcia'),
    ('Work Colleagues', 'Shared expenses for work activities', 'PedroMartinez');

INSERT INTO community_members (member, community)
VALUES
    ('JuanPerez', 1),
    ('MariaGarcia', 1),
    ('PedroMartinez', 1),
    ('MariaGarcia', 2),
    ('JuanPerez', 3),
    ('PedroMartinez', 3),
    ('LauraLopez', 3);

INSERT INTO expenses (community, expense_description, total_amount, payer)
VALUES
    (1, 'Group Dinner', 50.00, 'JuanPerez'),
    (1, 'Beer', 20.00, 'MariaGarcia'),
    (2, 'Birthday Gift', 100.00, 'PedroMartinez'),
    (3, 'Work Lunch', 30.00, 'JuanPerez');

INSERT INTO expense_participants (expense, member, amount, ratio_percentage)
VALUES
    (1, 'JuanPerez', 25.00, 0.5),
    (1, 'MariaGarcia', 25.00, 0.5),
    (2, 'JuanPerez', 20.00, 1.0),
    (3, 'PedroMartinez', 100.00, 1.0),
    (4, 'JuanPerez', 30.00, 1.0);

INSERT INTO debts (debtor, creditor, community, amount, status)
VALUES
    ('MariaGarcia', 'JuanPerez', 1, 25.00, 'pending'),
    ('PedroMartinez', 'JuanPerez', 1, 25.00, 'pending'),
    ('MariaGarcia', 'PedroMartinez', 2, 100.00, 'pending');

-- Create a new user
CREATE USER 'eazypay'@'localhost' IDENTIFIED BY 'eazypaypebb';

-- Grant privileges to the user on the eazypay database
GRANT ALL PRIVILEGES ON eazypay.* TO 'eazypay'@'localhost' WITH GRANT OPTION;

-- Apply the changes
FLUSH PRIVILEGES;
