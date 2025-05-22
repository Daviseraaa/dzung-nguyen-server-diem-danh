// public/js/login.js
import { fetchCsrfToken, showError, hideError } from './utils.js';
import { checkSession } from './session.js';
import { API_URLS } from './config.js';

// Quản lý các phần tử DOM
const DOM = {
    loginForm: document.getElementById('loginForm'),
    loginBtn: document.getElementById('loginBtn'),
    loadingSpinner: document.getElementById('loadingSpinner'),
    csrfTokenInput: document.getElementById('csrfToken'),
    usernameInput: document.getElementById('username'),
    passwordInput: document.getElementById('password')
};

/**
 * Xử lý trạng thái loading của nút đăng nhập.
 * @param {boolean} isLoading - True nếu đang loading, false nếu không
 */
function toggleLoadingState(isLoading) {
    DOM.loginBtn.disabled = isLoading;
    DOM.loadingSpinner.style.display = isLoading ? 'block' : 'none';
}

/**
 * Xử lý đăng nhập.
 * @param {Event} e - Sự kiện submit form
 */
async function handleLogin(e) {
    e.preventDefault();

    // Reset thông báo lỗi
    hideError();

    // Hiển thị loading
    toggleLoadingState(true);

    try {
        // Lấy dữ liệu từ form
        const username = DOM.usernameInput.value.trim();
        const password = DOM.passwordInput.value;
        const csrfToken = DOM.csrfTokenInput.value;

        // Kiểm tra input (đã có thuộc tính required trong HTML, nhưng thêm kiểm tra phía client để an toàn)
        if (!username || !password) {
            throw new Error('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
        }

        // Tạo form data
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('csrf_token', csrfToken);

        // Gửi yêu cầu đăng nhập
        const response = await fetch(API_URLS.LOGIN, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 401 && data.message === "Session expired. Please log in again.") {
                window.location.href = data.redirect;
                return;
            }

            if (response.status === 403 && data.error === "Invalid CSRF token") {
                throw new Error("Lỗi bảo mật: CSRF token không hợp lệ. Vui lòng tải lại trang.");
            }

            throw new Error(data.error || 'Lỗi không xác định');
        }

        // Đăng nhập thành công
        if (data.redirect) {
            window.location.href = data.redirect;
        }

    } catch (error) {
        showError(`${error.message} (Chi tiết: ${error.details || 'Không có'})`);
        console.error('Login Error:', error);
    } finally {
        toggleLoadingState(false);
    }
}

/**
 * Khởi tạo trang đăng nhập.
 */
async function initialize() {
    // Kiểm tra session trước
    const isLoggedIn = await checkSession();
    if (isLoggedIn) return;

    // Lấy CSRF token
    try {
        const token = await fetchCsrfToken();
        DOM.csrfTokenInput.value = token;
    } catch (error) {
        showError('Không thể lấy CSRF token. Vui lòng tải lại trang.');
        console.error(error.message);
    }

    // Gắn sự kiện submit form
    DOM.loginForm.addEventListener('submit', handleLogin);
}

// Khởi tạo khi trang tải
document.addEventListener('DOMContentLoaded', initialize);

// Export hàm để tái sử dụng (nếu cần)
export { handleLogin, initialize };