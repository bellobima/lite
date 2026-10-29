<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'fcwm_complaints';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $database)) {
        throw new RuntimeException('DB_NAME may contain only letters, numbers, and underscores.');
    }

    $server = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $schema = [
        "CREATE TABLE IF NOT EXISTS departments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(160) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            matric_number VARCHAR(60) NULL UNIQUE,
            department_id INT UNSIGNED NULL,
            role ENUM('student','department','admin') NOT NULL DEFAULT 'student',
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS tickets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference VARCHAR(32) NOT NULL UNIQUE,
            student_id INT UNSIGNED NOT NULL,
            department_id INT UNSIGNED NULL,
            category VARCHAR(100) NOT NULL,
            subject VARCHAR(180) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('Submitted','In Progress','Resolved','Closed') NOT NULL DEFAULT 'Submitted',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tickets_department_status (department_id, status),
            INDEX idx_tickets_student (student_id),
            CONSTRAINT fk_tickets_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_tickets_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS ticket_updates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ticket_id BIGINT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NULL,
            status VARCHAR(30) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_updates (ticket_id, created_at),
            CONSTRAINT fk_updates_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            CONSTRAINT fk_updates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
    ];
    foreach ($schema as $statement) {
        $pdo->exec($statement);
    }

    $departments = [
        'Wildlife Management',
        'Fisheries and Aquaculture',
        'Forestry and Environmental Management',
        'General Studies',
        'Student Affairs',
        'ICT and Library Services',
        'Works and Maintenance',
        'Registry and Admissions',
    ];
    $insertDepartment = $pdo->prepare('INSERT IGNORE INTO departments (name) VALUES (?)');
    foreach ($departments as $department) {
        $insertDepartment->execute([$department]);
    }

    return $pdo;
}
