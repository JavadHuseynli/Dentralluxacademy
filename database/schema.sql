-- Dental Academy Database Schema
-- MySQL / MariaDB

-- Create Database
CREATE DATABASE IF NOT EXISTS dental_academy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE dental_academy;

-- Instructors (Həkimlər) Table
CREATE TABLE IF NOT EXISTS instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    specialty VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    bio TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses (Kurslar) Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_az VARCHAR(255),
    category_en VARCHAR(255),
    category_ru VARCHAR(255),
    title_az VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    title_ru VARCHAR(255),
    description_az TEXT,
    description_en TEXT,
    description_ru TEXT,
    location_az VARCHAR(255),
    location_en VARCHAR(255),
    location_ru VARCHAR(255),
    course_date DATE NOT NULL,
    day_az VARCHAR(50),
    day_en VARCHAR(50),
    day_ru VARCHAR(50),
    payment_amount DECIMAL(10, 2),
    payment_currency VARCHAR(10) DEFAULT 'AZN',
    total_seats INT DEFAULT 20,
    available_seats INT DEFAULT 20,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (course_date),
    INDEX idx_active (is_active),
    INDEX idx_category (category_az)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Schedule Table
CREATE TABLE IF NOT EXISTS course_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    topic_az VARCHAR(255),
    topic_en VARCHAR(255),
    topic_ru VARCHAR(255),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Instructors (Many-to-Many) Table
CREATE TABLE IF NOT EXISTS course_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    role VARCHAR(100),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_instructor (course_id, instructor_id),
    INDEX idx_course (course_id),
    INDEX idx_instructor (instructor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrations (Qeydiyyatlar) Table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    academic_title VARCHAR(100),
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    medical_specialty VARCHAR(255),
    work_experience VARCHAR(255),
    join_reason TEXT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    badge_generated BOOLEAN DEFAULT FALSE,
    badge_code VARCHAR(50) UNIQUE,
    qr_code_url VARCHAR(500),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_badge (badge_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teacher Applications Table
CREATE TABLE IF NOT EXISTS teacher_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_title VARCHAR(100),
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    medical_specialty VARCHAR(255),
    work_experience VARCHAR(255),
    preferred_course VARCHAR(255),
    join_reason TEXT,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Dental Academy', 'Website name'),
('contact_email', 'info@dentalacademy.az', 'Contact email'),
('contact_phone', '+994 XX XXX XX XX', 'Contact phone'),
('default_language', 'az', 'Default language'),
('max_course_seats', '20', 'Default maximum seats per course'),
('enable_registrations', '1', 'Enable course registrations')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Views for easier queries

-- Active Courses View
CREATE OR REPLACE VIEW active_courses AS
SELECT
    c.*,
    GROUP_CONCAT(CONCAT(i.title, ' ', i.name) SEPARATOR ', ') as instructors,
    COUNT(DISTINCT r.id) as registration_count
FROM courses c
LEFT JOIN course_instructors ci ON c.id = ci.course_id
LEFT JOIN instructors i ON ci.instructor_id = i.id
LEFT JOIN registrations r ON c.id = r.course_id AND r.status = 'approved'
WHERE c.is_active = TRUE AND c.course_date >= CURDATE()
GROUP BY c.id;

-- Course Stats View
CREATE OR REPLACE VIEW course_stats AS
SELECT
    c.id,
    c.title_az,
    c.course_date,
    c.total_seats,
    c.available_seats,
    COUNT(DISTINCT r.id) as total_registrations,
    COUNT(DISTINCT CASE WHEN r.status = 'approved' THEN r.id END) as approved_registrations,
    COUNT(DISTINCT CASE WHEN r.status = 'pending' THEN r.id END) as pending_registrations,
    COUNT(DISTINCT ci.instructor_id) as instructor_count
FROM courses c
LEFT JOIN registrations r ON c.id = r.course_id
LEFT JOIN course_instructors ci ON c.id = ci.course_id
GROUP BY c.id;

-- Sample Data (optional)

-- Insert sample instructors
INSERT INTO instructors (name, title, specialty, email, phone, bio, image_url) VALUES
('Dr. Ayfer Atav', 'Dos. Dr.', 'Endodentiya', 'ayfer.atav@example.com', '+994 50 123 45 67', 'Endodontiya üzrə 15 illik təcrübə', 'img/docs/ayfer-atav.jpg'),
('Dr. Emre Övsay', 'Dr.', 'Endodentiya', 'emre.ovsay@example.com', '+994 50 234 56 78', 'Endodontiya mütəxəssisi', 'img/docs/emre-ovsay.jpg'),
('Dr. Gözde Çoban', 'Dr.', 'Ortodontiya', 'gozde.coban@example.com', '+994 50 345 67 89', 'Ortodontiya və Invisalign mütəxəssisi', 'img/docs/gozdecoban.png');

-- Stored Procedures

DELIMITER //

-- Procedure to register for a course
CREATE PROCEDURE RegisterForCourse(
    IN p_course_id INT,
    IN p_full_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_phone VARCHAR(50)
)
BEGIN
    DECLARE available INT;
    DECLARE badge_code VARCHAR(50);

    -- Check available seats
    SELECT available_seats INTO available FROM courses WHERE id = p_course_id;

    IF available > 0 THEN
        -- Generate badge code
        SET badge_code = CONCAT('DA-', UPPER(SUBSTRING(MD5(RAND()), 1, 8)));

        -- Insert registration
        INSERT INTO registrations (course_id, full_name, email, phone_number, badge_code)
        VALUES (p_course_id, p_full_name, p_email, p_phone, badge_code);

        -- Update available seats
        UPDATE courses SET available_seats = available_seats - 1 WHERE id = p_course_id;

        SELECT 'success' as status, badge_code, LAST_INSERT_ID() as registration_id;
    ELSE
        SELECT 'error' as status, 'No seats available' as message;
    END IF;
END //

-- Procedure to get upcoming courses
CREATE PROCEDURE GetUpcomingCourses(
    IN p_limit INT
)
BEGIN
    SELECT * FROM active_courses
    ORDER BY course_date ASC
    LIMIT p_limit;
END //

DELIMITER ;

-- Triggers

-- Update available seats after registration
DELIMITER //

CREATE TRIGGER after_registration_insert
AFTER INSERT ON registrations
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE courses
        SET available_seats = GREATEST(0, available_seats - 1)
        WHERE id = NEW.course_id;
    END IF;
END //

CREATE TRIGGER after_registration_delete
AFTER DELETE ON registrations
FOR EACH ROW
BEGIN
    IF OLD.status = 'approved' THEN
        UPDATE courses
        SET available_seats = LEAST(total_seats, available_seats + 1)
        WHERE id = OLD.course_id;
    END IF;
END //

DELIMITER ;

-- Grant privileges (adjust username/password as needed)
-- CREATE USER 'dental_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT ALL PRIVILEGES ON dental_academy.* TO 'dental_user'@'localhost';
-- FLUSH PRIVILEGES;
