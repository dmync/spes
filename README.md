# Sales Performance Evaluation System (SPES)

**Life Backup – Sales Performance Platform**

A web-based centralized sales performance management system developed as a university assignment.

## About the Project

The Sales Performance Evaluation System (SPES) is a web-based application developed for **Life Backup**, a life insurance policy-selling company with branches across the country.

The system is designed to manage the company's sales force, organizational hierarchy, recruitment activities, sales targets, policy sales, and premium collections through a centralized database.

It provides different levels of access for managers, supervisors, team leaders, life insurance advisors, sales agents, and cashiers.

The main objective of this project is to simplify sales performance monitoring and help management evaluate employee and branch performance based on monthly sales targets.

## Project Objectives

- Develop a centralized sales performance management system.
- Implement role-based authentication and access.
- Manage the organizational hierarchy of the sales force.
- Monitor monthly policy sales and premium collection targets.
- Manage recruitment, promotions, transfers, and resignations.
- Record insurance premium collection invoices.
- Provide dashboards and reports for different management levels.

## User Roles

The system supports six main user roles:

| Role | Responsibilities |
|---|---|
| Manager | Manage branch-level operations, recruitment, promotions, and approvals |
| Supervisor | Manage team leaders, agents, and team activities |
| Team Leader | Manage assigned sales agents and monitor team performance |
| Life Insurance Advisor (LIA) | Manage life insurance sales activities |
| Sales Agent | Introduce new life insurance policies and collect premiums |
| Cashier | Enter and manage premium collection invoice details |

## Key Features

### 1. Role-Based Login System

- Common login interface for all users.
- Role selection before authentication.
- Login using agent code and password.
- Role-based access to relevant system functions.
- Separate cashier interface.

### 2. Employee and Recruitment Management

- Recruit new supervisors.
- Recruit team leaders.
- Recruit sales agents.
- Recruit life insurance advisors.
- Manage employee information and organizational assignments.

### 3. Organizational Hierarchy Management

The system follows the organizational structure of Life Backup:

```text
Branch Manager
│
├── Sales Supervisors
│   │
│   ├── Team Leaders
│   │   │
│   │   └── Sales Agents
│   │
│   └── Sales Agents
│
├── Life Insurance Advisors
│
└── Cashier
```

### 4. Promotion Management

- Manage promotion requests.
- Promote sales agents to team leaders.
- Promote team leaders to supervisors.
- Promote supervisors to branch managers.
- Support promotion to Life Insurance Advisor roles.

### 5. Transfer Management

- Change an agent's assigned supervisor.
- Change an agent's team leader.
- Transfer employees between branches.
- Update organizational assignments.

### 6. Resignation Management

- Submit resignation requests.
- Manage employee resignation activities.
- Maintain resignation-related records.

### 7. Sales Target Management

The system supports monthly sales targets based on employee roles.

| Role | Monthly New Policy Target | Monthly Premium Target |
|---|---:|---:|
| Life Insurance Advisor | 5 policies | LKR 750,000 |
| Team Leader | 3 policies | LKR 500,000 |
| Sales Agent | 5 policies | LKR 300,000 |
| Supervisor | Based on team targets | Based on team targets |
| Branch Manager | Based on branch targets | Based on branch targets |

### 8. Cashier Invoice Management

The cashier module provides a dedicated interface for entering premium collection invoice details.

Invoice information includes:

- Branch Code
- Supervisor Code
- Team Leader Code
- Sales Agent Code
- Invoice Date
- Policy Number
- Premium Amount (LKR)
- Payment Frequency
- Agent Signature
- Cash Handed Over Date

Supported payment frequencies:

- Monthly
- Quarterly
- Bi-Annual
- Annual

The interface also provides an option to export invoice details as PDF.

### 9. Dashboard and Performance Reports

The system includes dashboard and reporting functionality for monitoring sales activities.

Performance evaluation focuses on:

- Number of new insurance policies.
- Total premium collection.
- Monthly sales targets.
- Recruitment activities.
- Organizational performance.
- Agent and team-related reports.

## Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript
- Bootstrap 5.3.2
- Bootstrap Icons 1.11.3
- jQuery 3.7.0
- Chart.js 4.4.0

### Backend
- PHP

### Database
- MySQL

### Communication
- AJAX

## Project Structure

```text
SPES/
│
├── api/
│   ├── activity_log.php
│   ├── activity_log_list.php
│   ├── add_agent.php
│   ├── agent_reports.php
│   ├── approve_promotion.php
│   ├── approve_request.php
│   ├── get_dashboard.php
│   ├── get_dropdowns.php
│   ├── get_reports.php
│   ├── lia_reports.php
│   ├── list_promotions.php
│   ├── login.php
│   ├── logout.php
│   ├── manage_targets.php
│   ├── org_tree.php
│   ├── promote.php
│   ├── recruits_list.php
│   ├── resign.php
│   ├── save_invoice.php
│   ├── supervisor_requests.php
│   ├── supervisor_tl_list.php
│   ├── teamleader_dashboard.php
│   ├── tl_agents_list.php
│   ├── tl_requests.php
│   └── transfer.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── img/
│   │   └── logo.png
│   └── js/
│       └── app.js
│
├── db/
│   └── init.sql
│
├── db_connect.php
├── index.php
├── page.php
└── README.md
```

## Installation and Setup

### Prerequisites

- XAMPP
- PHP
- MySQL
- Modern web browser

### 1. Clone the Repository

```bash
git clone https://github.com/dmync/spes.git
```

### 2. Move the Project

Copy the project folder into the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\spes
```

### 3. Start Apache and MySQL

Open XAMPP Control Panel and start:

- Apache
- MySQL

### 4. Create the Database

1. Open phpMyAdmin.
2. Create a new MySQL database.
3. Select the newly created database.
4. Import the `db/init.sql` file.

### 5. Configure Database Connection

Open `db_connect.php` and configure the following details according to your local MySQL environment:

- Database host
- Database username
- Database password
- Database name

### 6. Run the Application

Open your browser and visit:

```text
http://localhost/spes/page.php#login
```

## Project Type

**University Assignment / Academic Project**

This project was developed as an academic assignment based on the provided Life Backup Sales Performance Evaluation System scenario.

## Repository

GitHub: https://github.com/dmync/spes

## Author

**GitHub:** [@dmync](https://github.com/dmync)

---

*Sales Performance Evaluation System – Life Backup*
