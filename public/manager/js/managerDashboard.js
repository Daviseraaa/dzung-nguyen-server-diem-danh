// public/manager/js/dashboard.js
import { setupEventListeners } from './eventHandlers.js';

// Hàm kiểm tra session và lấy teacher_id
async function checkSessionAndSetUser() {
    try {
        const res = await fetch('/server_diem_danh/api/check_session.php');
        const data = await res.json();
        if (!data.logged_in || data.role !== 'manager') {
            window.location.href = '/server_diem_danh/public/login.html';
            return false;
        }
        window.teacher_id = data.teacher_id || data.user_id; // Ưu tiên teacher_id nếu có
        window.username = data.username;
        return true;
    } catch (e) {
        window.location.href = '/server_diem_danh/public/login.html';
        return false;
    }
}

// Khởi tạo khi trang tải
document.addEventListener('DOMContentLoaded', async () => {
    if (await checkSessionAndSetUser()) {
        setupEventListeners();
    }
});