// public/student/js/eventHandlers.js
import { validateFilters, createFilterObject, resetFilters } from '/server_diem_danh/public/assets/js/filterUtils.js';
import { displayAttendance } from './personalAttendance.js';
import { logout } from '/server_diem_danh/public/assets/js/utils.js';

// Quản lý các phần tử DOM liên quan đến sự kiện
const DOM = {
    filterDate: document.getElementById('filterDate'),
    filterStartTime: document.getElementById('filterStartTime'),
    filterEndTime: document.getElementById('filterEndTime'),
    filterRoom: document.getElementById('filterRoom'),
    filterBtn: document.getElementById('filterBtn'),
    resetBtn: document.getElementById('resetBtn'),
    logoutBtn: document.getElementById('logoutBtn')
};

/**
 * Thiết lập tất cả các sự kiện DOM cho dashboard sinh viên.
 */
export function setupEventListeners() {
    // Sự kiện cho nút lọc
    DOM.filterBtn.addEventListener('click', async () => {
        const date = DOM.filterDate.value;
        const startTime = DOM.filterStartTime.value;
        const endTime = DOM.filterEndTime.value;
        const room = DOM.filterRoom.value;

        if (!validateFilters(date, startTime, endTime, room)) return;

        const filters = createFilterObject(date, startTime, endTime, room);
        await displayAttendance(filters);
    });

    // Sự kiện cho nút đặt lại
    DOM.resetBtn.addEventListener('click', () => {
        resetFilters(['filterDate', 'filterStartTime', 'filterEndTime', 'filterRoom'], 'timeFilter');
        displayAttendance();
    });

    // Sự kiện đăng xuất
    DOM.logoutBtn.addEventListener('click', logout);
}