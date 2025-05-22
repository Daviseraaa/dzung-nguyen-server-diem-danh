import { loadAttendance, loadRooms } from '/server_diem_danh/public/assets/js/attendanceFilter.js';
import { handleError } from '/server_diem_danh/public/assets/js/utils.js';

/**
 * Tạo một hàng HTML cho điểm danh.
 * @param {Object} record - Đối tượng điểm danh
 * @returns {string} - Chuỗi HTML của hàng điểm danh
 */
function createAttendanceRow(record) {
    return `
        <tr>
            <td>${record.attendance_id}</td>
            <td>${record.student_id}</td>
            <td>${record.checkin_time}</td>
            <td>${record.room}</td>
        </tr>
    `;
}

/**
 * Hiển thị dữ liệu điểm danh lên bảng.
 * @param {Object} filters - Bộ lọc để load dữ liệu điểm danh
 */
export async function displayAttendance(filters = {}) {
    try {
        const attendance = await loadAttendance(filters);
        const attendanceList = document.getElementById('attendanceList');
        attendanceList.innerHTML = '';
        if (attendance.length === 0) {
            attendanceList.innerHTML = '<tr><td colspan="5" class="text-center">Không có dữ liệu điểm danh</td></tr>';
        } else {
            attendance.forEach(record => {
                attendanceList.innerHTML += createAttendanceRow(record);
            });
        }
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi load thông tin điểm danh');
    }
}

/**
 * Load danh sách phòng học và cập nhật dropdown.
 */
export async function populateRooms() {
    try {
        const rooms = await loadRooms();
        const roomSelect = document.getElementById('filterRoom');
        roomSelect.innerHTML = '<option value="">Tất cả</option>';
        rooms.forEach(room => {
            roomSelect.innerHTML += `<option value="${room}">${room}</option>`;
        });
    } catch (error) {
        handleError(error, 'Không thể load danh sách phòng');
    }
}