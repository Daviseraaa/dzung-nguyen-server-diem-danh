<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Thiết lập security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Xử lý logic khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->autocommit(FALSE); // Bắt đầu transaction
    
        // Lấy và validate dữ liệu (giữ nguyên)
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $student_id = trim($_POST['student_id'] ?? '');
        $rfid = trim($_POST['rfid'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $class = trim($_POST['class'] ?? '');
    
        // Validate (giữ nguyên)
        if (empty($username) || empty($password) || empty($role)) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
    
        if (!in_array($role, ['admin', 'manager', 'student'])) {
            throw new Exception("Vai trò không hợp lệ");
        }
    
        // Thêm vào bảng STUDENTS trước nếu là sinh viên
        if ($role === 'student') {
            if (empty($student_id) || empty($rfid) || empty($fullname) || empty($class)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin sinh viên");
            }
    
            // Kiểm tra student_id đã tồn tại chưa
            $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Mã sinh viên đã tồn tại");
            }
    
            // Thêm vào bảng students
            $stmt = $conn->prepare("INSERT INTO students (student_id, rfid_uid, full_name, class) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $student_id, $rfid, $fullname, $class);
            $stmt->execute();
        }
    
        // Kiểm tra username tồn tại (giữ nguyên)
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Username đã tồn tại");
        }
    
        // Hash password (giữ nguyên)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // Thêm vào bảng USERS
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?, ?, ?, ?)");
        $student_id_value = $role === 'student' ? $student_id : NULL; // Chỉ gán student_id nếu là sinh viên
        $stmt->bind_param("ssss", $username, $hashedPassword, $role, $student_id_value);
        $stmt->execute();
    
        $conn->commit();
        $success = "Tạo tài khoản thành công!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    } finally {
        $conn->autocommit(TRUE);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Tài Khoản Testing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .student-fields {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Tạo Tài Khoản Testing</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post">
                <!-- Thông tin chung -->
                <div class="mb-3">
                    <label class="form-label">Username*</label>
                    <input type="text" class="form-control" name="username" required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password*</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vai trò*</label>
                    <select class="form-select" name="role" id="roleSelect" required>
                        <option value="">Chọn vai trò</option>
                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Quản lý lớp</option>
                        <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>Sinh viên</option>
                    </select>
                </div>

                <!-- Thông tin sinh viên -->
                <div class="student-fields" id="studentFields">
                    <div class="mb-3">
                        <label class="form-label">Mã sinh viên*</label>
                        <input type="text" class="form-control" name="student_id"
                            value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RFID UID*</label>
                        <input type="text" class="form-control" name="rfid"
                            value="<?= htmlspecialchars($_POST['rfid'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Họ tên*</label>
                        <input type="text" class="form-control" name="fullname"
                            value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lớp*</label>
                        <input type="text" class="form-control" name="class"
                            value="<?= htmlspecialchars($_POST['class'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Tạo tài khoản</button>
            </form>
        </div>
    </div>

    <script>
        // Hiển thị trường sinh viên khi chọn role student
        const roleSelect = document.getElementById('roleSelect');
        const studentFields = document.getElementById('studentFields');

        function toggleStudentFields() {
            studentFields.style.display = roleSelect.value === 'student' ? 'block' : 'none';
            // Đặt required cho các trường sinh viên
            const studentInputs = studentFields.querySelectorAll('input');
            studentInputs.forEach(input => {
                input.required = roleSelect.value === 'student';
            });
        }

        roleSelect.addEventListener('change', toggleStudentFields);
        toggleStudentFields(); // Khởi tạo ban đầu
    </script>
</body>
</html>