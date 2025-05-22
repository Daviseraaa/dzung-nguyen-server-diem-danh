CREATE DATABASE attendance_test;
USE attendance_test;

CREATE TABLE students (
    student_id VARCHAR(20) PRIMARY KEY,
    rfid_uid VARCHAR(50) UNIQUE,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teachers (
    teacher_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    course_id VARCHAR(20) PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    class_id VARCHAR(20) PRIMARY KEY,
    course_id VARCHAR(20),
    teacher_id VARCHAR(20),
    class_name VARCHAR(100) NOT NULL,
    semester VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id)
);

CREATE TABLE class_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id VARCHAR(20),
    student_id VARCHAR(20),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    UNIQUE (class_id, student_id)
);

CREATE TABLE class_schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id VARCHAR(20),
    room VARCHAR(10),
    start_time TIME,
    end_time TIME,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20),
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    room VARCHAR(10),
    class_id VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'student', 'teacher') NOT NULL,
    student_id VARCHAR(20) DEFAULT NULL,
    teacher_id VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id)
);

-- Tạo chỉ mục để tối ưu truy vấn
CREATE INDEX idx_rfid_uid ON students(rfid_uid);
CREATE INDEX idx_class_registrations ON class_registrations(student_id, class_id);
CREATE INDEX idx_class_schedules ON class_schedules(class_id, room, day_of_week, start_time, end_time);
CREATE INDEX idx_attendance ON attendance(student_id, class_id, checkin_time);