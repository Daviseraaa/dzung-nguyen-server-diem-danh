// public/student/js/studentDashboard.js
import { initializeProfile } from './profileManagement.js';
import { initializeAttendance, initializeAttendanceSummary } from './personalAttendance.js';
import { setupEventListeners } from './eventHandlers.js';
import { setupTimeFilter } from '/server_diem_danh/public/assets/js/attendanceFilter.js';

// Khởi tạo khi trang tải
document.addEventListener('DOMContentLoaded', async () => {
    await initializeProfile();
    await initializeAttendance();
    await initializeAttendanceSummary();
    setupTimeFilter('filterDate', 'timeFilter', 'filterStartTime', 'filterEndTime');
    setupEventListeners();
});