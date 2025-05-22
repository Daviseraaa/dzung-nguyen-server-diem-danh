// public/admin/js/eventHandlers.js
import { loadStudents, addStudent, editStudent, deleteStudent, importStudentsFromExcel } from './studentManagement.js';
import { displayAttendance } from './attendanceManagement.js';
import { validateFilters, createFilterObject, resetFilters } from '/server_diem_danh/public/assets/js/filterUtils.js';
import { logout } from '/server_diem_danh/public/assets/js/utils.js';

/**
 * Thiết lập tất cả các sự kiện DOM cho dashboard admin.
 */
export function setupEventListeners() {
    // Event delegation cho bảng sinh viên
    document.getElementById('studentList').addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;

        const studentId = row.cells[0].textContent;
        const fullName = row.cells[1].textContent;
        const studentClass = row.cells[2].textContent;
        const rfidUid = row.cells[3].textContent;

        if (e.target.classList.contains('edit-btn')) {
            document.getElementById('editStudentId').value = studentId;
            document.getElementById('editFullName').value = fullName;
            document.getElementById('editClass').value = studentClass;
            document.getElementById('editRfidUid').value = rfidUid;
            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        } else if (e.target.classList.contains('delete-btn')) {
            if (confirm(`Xóa sinh viên ${studentId}?`)) {
                deleteStudent(studentId).then(success => {
                    if (success) loadStudents();
                });
            }
        }
    });

    // Submit form thêm sinh viên
    document.getElementById('addStudentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const studentData = {
            student_id: document.getElementById('studentId').value,
            full_name: document.getElementById('fullName').value,
            class: document.getElementById('class').value,
            rfid_uid: document.getElementById('rfidUid').value
        };
        const success = await addStudent(studentData);
        if (success) {
            loadStudents();
            bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
        }
    });

    // Thêm sinh viên bằng file excel
    document.getElementById('importStudentsForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('excelFile');
        const file = fileInput.files[0];
        if (!file) {
            alert('Vui lòng chọn file Excel');
            return;
        }
        const success = await importStudentsFromExcel(file);
        if (success) {
            loadStudents();
            bootstrap.Modal.getInstance(document.getElementById('importStudentsModal')).hide();
        }
    });

    // Submit form chỉnh sửa sinh viên
    document.getElementById('editStudentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const studentData = {
            student_id: document.getElementById('editStudentId').value,
            full_name: document.getElementById('editFullName').value,
            class: document.getElementById('editClass').value,
            rfid_uid: document.getElementById('editRfidUid').value
        };
        const success = await editStudent(studentData);
        if (success) {
            loadStudents();
            bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
        }
    });

    // Sự kiện cho nút lọc
    document.getElementById('filterBtn').addEventListener('click', () => {
        const date = document.getElementById('filterDate').value;
        const startTime = document.getElementById('filterStartTime').value;
        const endTime = document.getElementById('filterEndTime').value;
        const room = document.getElementById('filterRoom').value;

        if (!validateFilters(date, startTime, endTime, room)) return;

        const filters = createFilterObject(date, startTime, endTime, room);
        displayAttendance(filters);
    });

    // Sự kiện cho nút đặt lại
    document.getElementById('resetBtn').addEventListener('click', () => {
        resetFilters(['filterDate', 'filterStartTime', 'filterEndTime', 'filterRoom'], 'timeFilter');
        displayAttendance();
    });

    // Sự kiện đăng xuất
    document.getElementById('logoutBtn').addEventListener('click', logout);
}