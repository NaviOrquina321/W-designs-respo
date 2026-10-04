-- database.sql
-- Barangay Resident Information System (BRIS) - MySQL Database Schema & Sample Records

CREATE DATABASE IF NOT EXISTS bris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bris_db;

CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(20) DEFAULT '',
    birthdate DATE NOT NULL,
    age INT NOT NULL,
    sex VARCHAR(20) NOT NULL,
    civil_status VARCHAR(30) NOT NULL,
    purok VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    occupation VARCHAR(100) DEFAULT 'N/A',
    contact_number VARCHAR(30) DEFAULT '',
    voter_status VARCHAR(50) NOT NULL DEFAULT 'Not Registered',
    status VARCHAR(50) NOT NULL DEFAULT 'Active',
    archived TINYINT(1) NOT NULL DEFAULT 0,
    archive_reason TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO residents (
    first_name, middle_name, last_name, suffix, birthdate, age, sex,
    civil_status, purok, address, occupation, contact_number,
    voter_status, status, archived, archive_reason
) VALUES
('Juan', 'Santos', 'Dela Cruz', '', '1988-05-12', 36, 'Male', 'Married', 'Purok 1', '123 Mahogany St., Purok 1', 'Barangay Health Worker', '09171234567', 'Registered', 'Active', 0, ''),
('Maria Clara', 'Reyes', 'Santos', '', '1995-10-24', 29, 'Female', 'Single', 'Purok 2', '45 Sampaguita St., Purok 2', 'Public School Teacher', '09189876543', 'Registered', 'Active', 0, ''),
('Jose', 'Protacio', 'Rizal', 'Jr.', '1961-06-19', 63, 'Male', 'Married', 'Purok 3', '78 Ilang-Ilang Lane, Purok 3', 'Physician', '09201112233', 'Registered', 'Active', 0, ''),
('Andres', 'Castro', 'Bonifacio', '', '1975-11-30', 49, 'Male', 'Married', 'Purok 4', '12 Balagtas St., Purok 4', 'Business Owner', '09175554433', 'Registered', 'Active', 0, ''),
('Melchora', 'Aquino', 'Ramos', '', '1952-01-06', 72, 'Female', 'Widowed', 'Purok 5', '88 Banaba Drive, Purok 5', 'Retired', '09223334455', 'Registered', 'Active', 0, ''),
('Emilio', 'Famy', 'Aguinaldo', '', '1999-03-22', 25, 'Male', 'Single', 'Purok 6', '101 Narra Ave., Purok 6', 'IT Specialist', '09198887766', 'Not Registered', 'Active', 0, ''),
('Gabriela', 'Silang', 'Cariño', '', '2008-08-15', 16, 'Female', 'Single', 'Purok 7', '55 Acacia St., Purok 7', 'Student', '09170001122', 'Not Registered', 'Active', 0, ''),
('Apolinario', 'Maranan', 'Mabini', '', '1970-07-23', 54, 'Male', 'Single', 'Purok 1', '33 Mabini St., Purok 1', 'Legal Consultant', '09183332211', 'Registered', 'Moved Out', 1, 'Relocated to Manila');
