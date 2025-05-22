// public/student/js/personalAttendance.js
// Định nghĩa các hàm API riêng cho student
async function loadStudentAttendance(filters = {}) {
    const response = await fetch('/server_diem_danh/api/student/studentAttendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(filters)
    });
    const data = await response.json();
    if (!data.success) throw new Error(data.error || 'Không thể tải lịch sử điểm danh');
    return data.attendance;
}

async function loadStudentRooms() {
    const response = await fetch('/server_diem_danh/api/student/studentAttendance.php?action=rooms');
    const data = await response.json();
    if (!data.success) throw new Error(data.error || 'Không thể tải danh sách phòng');
    return data.rooms;
}

import { handleError } from '/server_diem_danh/public/assets/js/utils.js';

const DOM = {
    filterRoom: document.getElementById('filterRoom'),
    attendanceList: document.getElementById('attendanceList'),
    classAttendanceList: document.getElementById('classAttendanceList')
};

/**
 * Tạo hàng HTML cho lịch sử điểm danh.
 * @param {Object} record - Bản ghi điểm danh
 * @returns {string} - Chuỗi HTML của hàng
 */
function createAttendanceRow(record) {
    return `
        <tr>
            <td>${record.student_id}</td>
            <td>${record.room}</td>
            <td>${new Date(record.checkin_time).toLocaleString('vi-VN')}</td>
        </tr>
    `;
}

/**
 * Hiển thị lịch sử điểm danh.
 * @param {Object} [filters={}] - Bộ lọc (date, startTime, endTime, room)
 */
async function displayAttendance(filters = {}) {
    try {
        const attendance = await loadStudentAttendance(filters);
        DOM.attendanceList.innerHTML = attendance.map(createAttendanceRow).join('');
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi tải lịch sử điểm danh');
    }
}

/**
 * Tạo bảng HTML cho lịch sử chuyên cần của một lớp.
 * @param {Object} classData - Dữ liệu chuyên cần của lớp
 * @returns {string} - Chuỗi HTML của bảng
 */
function createClassAttendanceTable(classData) {
    const { class_id, class_name, sessions } = classData;
    let html = `<h6>Lớp ${class_id} - ${class_name}</h6><table class="table table-striped"><thead><tr><th>Ngày học</th><th>Trạng thái</th></tr></thead><tbody>`;
    sessions.forEach(session => {
        html += `<tr><td>${new Date(session.date).toLocaleDateString('vi-VN')}</td><td>${session.status || 'Vắng'}</td></tr>`;
    });
    html += '</tbody></table><hr>';
    return html;
}

/**
 * Hiển thị lịch sử chuyên cần.
 */
async function displayAttendanceSummary() {
    try {
        // Gọi trực tiếp API studentAttendanceSummary.php
        const response = await fetch('/server_diem_danh/api/student/studentAttendanceSummary.php');
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Không thể tải lịch sử chuyên cần');
        const summary = data.summary;
        DOM.classAttendanceList.innerHTML = summary.map(createClassAttendanceTable).join('');
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi tải lịch sử chuyên cần');
    }
}

/**
 * Khởi tạo module điểm danh: load danh sách phòng và hiển thị điểm danh ban đầu.
 */
export async function initializeAttendance() {
    try {
        const rooms = await loadStudentRooms();
        DOM.filterRoom.innerHTML = '<option value="">Chọn phòng</option>';
        rooms.forEach(room => {
            DOM.filterRoom.innerHTML += `<option value="${room}">${room}</option>`;
        });
    } catch (error) {
        handleError(error, 'Không thể load danh sách phòng');
    }
    await displayAttendance();
}

/**
 * Khởi tạo module chuyên cần: tải và hiển thị lịch sử chuyên cần.
 */
export async function initializeAttendanceSummary() {
    await displayAttendanceSummary();
}

export { displayAttendance, displayAttendanceSummary };