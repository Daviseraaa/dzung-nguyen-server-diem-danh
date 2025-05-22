import { API_BASE_URL } from './config.js';
import { handleError } from '/server_diem_danh/public/assets/js/utils.js';
import { readExcelFile } from '/server_diem_danh/public/assets/js/excelUtils.js';

//File này chứa các hàm liên quan đến quản lý sinh viên như load, add, edit, delete.

/**
 * Tạo một hàng HTML cho sinh viên.
 * @param {Object} student - Đối tượng sinh viên
 * @returns {HTMLElement} - Phần tử <tr> chứa thông tin sinh viên
 */
function createStudentRow(student) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${student.student_id}</td>
        <td>${student.full_name}</td>
        <td>${student.class}</td>
        <td>${student.rfid_uid}</td>
        <td>
            <button class="btn btn-sm btn-warning edit-btn">Sửa</button>
            <button class="btn btn-sm btn-danger delete-btn">Xóa</button>
        </td>
    `;
    return row;
}

/**
 * Tải danh sách sinh viên từ server và hiển thị lên bảng.
 */
export async function loadStudents() {
    try {
        const response = await fetch(`${API_BASE_URL}?action=list`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await response.json();
        if (data.success) {
            const studentList = document.getElementById('studentList');
            studentList.innerHTML = '';
            data.students.forEach(student => {
                studentList.appendChild(createStudentRow(student));
            });
        } else {
            throw new Error(data.error || 'Không thể load danh sách sinh viên');
        }
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi load danh sách sinh viên');
    }
}

/**
 * Thêm sinh viên mới.
 * @param {Object} studentData - Dữ liệu sinh viên mới
 */
export async function addStudent(studentData) {
    try {
        const response = await fetch(`${API_BASE_URL}?action=add`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(studentData)
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Thêm sinh viên thất bại');
        }
        return true;
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi thêm sinh viên');
        return false;
    }
}

/**
 * Chỉnh sửa thông tin sinh viên.
 * @param {Object} studentData - Dữ liệu sinh viên cập nhật
 */
export async function editStudent(studentData) {
    try {
        const response = await fetch(`${API_BASE_URL}?action=edit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(studentData)
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Chỉnh sửa sinh viên thất bại');
        }
        return true;
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi chỉnh sửa sinh viên');
        return false;
    }
}

/**
 * Xóa sinh viên.
 * @param {string} studentId - Mã sinh viên cần xóa
 */
export async function deleteStudent(studentId) {
    try {
        const response = await fetch(`${API_BASE_URL}?action=delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Xóa sinh viên thất bại');
        }
        return true;
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi xóa sinh viên');
        return false;
    }
}

export async function importStudentsFromExcel(file) {
    try {
        const excelData = await readExcelFile(file);
        const studentData = excelData.map(row => ({
            student_id: row['Mã số sinh viên'],
            rfid_uid: row['RFID'],
            full_name: row['Họ và tên'],
            class: row['Lớp']
        }));
        const response = await fetch('/server_diem_danh/api/admin/bulk_add_students.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ students: studentData })
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            return true;
        } else {
            alert('Import thất bại: ' + result.message);
            return false;
        }
    } catch (error) {
        console.error('Error importing students:', error);
        alert('Đã có lỗi xảy ra khi import sinh viên');
        return false;
    }
}