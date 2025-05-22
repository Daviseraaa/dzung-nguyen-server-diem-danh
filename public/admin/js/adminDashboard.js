// public/admin/js/dashboard.js
import { loadStudents } from './studentManagement.js';
import { displayAttendance, populateRooms } from './attendanceManagement.js';
import { setupTimeFilter } from '/server_diem_danh/public/assets/js/attendanceFilter.js';
import { setupEventListeners } from './eventHandlers.js';

// Khởi tạo khi trang tải
document.addEventListener('DOMContentLoaded', () => {
    // Tải danh sách sinh viên
    loadStudents();

    // Thiết lập bộ lọc thời gian
    setupTimeFilter('filterDate', 'timeFilter', 'filterStartTime', 'filterEndTime');

    // Thiết lập các sự kiện DOM
    setupEventListeners();

    // Tải dữ liệu điểm danh khi tab được kích hoạt
    document.getElementById('attendance-tab').addEventListener('shown.bs.tab', () => {
        displayAttendance();
        populateRooms();
    });
});