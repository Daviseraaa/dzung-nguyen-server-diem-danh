// public/student/js/profileManagement.js
import { handleError } from '/server_diem_danh/public/assets/js/utils.js';

// Quản lý các phần tử DOM liên quan đến profile
const DOM = {
    studentId: document.getElementById('studentId'),
    fullName: document.getElementById('fullName'),
    rfidUid: document.getElementById('rfidUid')
    // Loại bỏ class và createdAt
};

/**
 * Tải thông tin sinh viên từ API và hiển thị lên giao diện.
 */
export async function initializeProfile() {
    try {
        const response = await fetch('/server_diem_danh/api/student/profile.php');
        
        const contentType = response.headers.get('Content-Type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Response is not JSON: ${text}`);
        }

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Không thể tải thông tin sinh viên');
        }

        DOM.studentId.textContent = data.student.student_id || 'N/A';
        DOM.fullName.textContent = data.student.full_name ? decodeURIComponent(data.student.full_name.replace(/\\u([\d\w]{4})/gi, '%u$1')) : 'N/A';
        DOM.rfidUid.textContent = data.student.rfid_uid || 'N/A';
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi tải thông tin sinh viên');
    }
}