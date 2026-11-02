-- db/init.sql
DROP DATABASE IF EXISTS spes_db;
CREATE DATABASE spes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spes_db;

CREATE TABLE branches (
  branch_id INT AUTO_INCREMENT PRIMARY KEY,
  branch_code VARCHAR(20) NOT NULL UNIQUE,
  branch_name VARCHAR(100) NOT NULL
);

CREATE TABLE agents (
  agent_id INT AUTO_INCREMENT PRIMARY KEY,
  agent_code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  role ENUM('manager','supervisor','team_leader','agent','lia','cashier') NOT NULL,
  branch_id INT DEFAULT NULL,
  supervisor_code VARCHAR(20) DEFAULT NULL,
  team_leader_code VARCHAR(20) DEFAULT NULL,
  status ENUM('active','resigned') DEFAULT 'active',
  promoted_from VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL
);

CREATE TABLE logins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_code VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE invoices (
  invoice_id INT AUTO_INCREMENT PRIMARY KEY,
  branch_code VARCHAR(20) NOT NULL,
  supervisor_code VARCHAR(20) DEFAULT NULL,
  team_leader_code VARCHAR(20) DEFAULT NULL,
  agent_code VARCHAR(20) DEFAULT NULL,
  invoice_date DATE NOT NULL,
  policy_number VARCHAR(50) NOT NULL,
  premium_amount DECIMAL(12,2) NOT NULL,
  payment_frequency VARCHAR(20) NOT NULL,
  agent_signature TINYINT(1) DEFAULT 0,
  cash_handed_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE promotions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_code VARCHAR(20) NOT NULL,
  from_role VARCHAR(50),
  to_role VARCHAR(50) NOT NULL,
  approved_by VARCHAR(20) DEFAULT NULL,
  reason TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  decided_at TIMESTAMP NULL
);

CREATE TABLE recruitment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entered_code VARCHAR(20) NOT NULL,
  branch_code VARCHAR(20) DEFAULT NULL,
  role_suggested VARCHAR(50) DEFAULT NULL,
  entered_by VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_code VARCHAR(20) DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- sample data
INSERT INTO branches (branch_code, branch_name) VALUES ('KB-01', 'Kesbewa First Branch');

INSERT INTO agents (agent_code, name, role, branch_id) VALUES
('00001','Chaminda Silva','manager',1),
('00002','Nimal Perera','supervisor',1),
('00003','Sunil Fernando','team_leader',1),
('00004','Kamal Wick','agent',1),
('00005','Saman Kumara','lia',1),
('C0001','Cashier One','cashier',1);

INSERT INTO logins (agent_code, password) VALUES
('00001','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK'),
('00002','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK'),
('00003','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK'),
('00004','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK'),
('00005','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK'),
('C0001','$2y$10$zG3kNd6/XkH1qN3D0rW6FeI6k7E7y9C2tku9rQ7JpY6F3K5k8M8xK');

INSERT INTO invoices (branch_code, supervisor_code, team_leader_code, agent_code, invoice_date, policy_number, premium_amount, payment_frequency, agent_signature, cash_handed_date)
VALUES ('KB-01','00002','00003','00004','2025-11-01','POL-1001',150000.00,'Monthly',1,'2025-11-02');
