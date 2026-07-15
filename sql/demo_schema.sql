-- OfficeHub Portfolio Showcase
-- Simplified illustrative schema. Complete production migrations are omitted.

CREATE TABLE demo_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    role VARCHAR(40) NOT NULL DEFAULT 'member',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE demo_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    assigned_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_demo_task_user FOREIGN KEY (assigned_user_id)
        REFERENCES demo_users(id) ON DELETE SET NULL
);

INSERT INTO demo_users (display_name, email, role) VALUES
('Demo Administrator', 'admin@example.test', 'admin'),
('Demo Analyst', 'analyst@example.test', 'member');
