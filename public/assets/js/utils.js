// public/js/utils.js

// Lấy CSRF token
async function fetchCsrfToken() {
    try {
        const response = await fetch('/server_diem_danh/api/get_csrf_token.php');
        if (!response.ok) {
            throw new Error('Failed to fetch CSRF token');
        }
        const data = await response.json();
        return data.token;
    } catch (error) {
        throw new Error('Error fetching CSRF token: ' + error.message);
    }
}

// Hiển thị thông báo lỗi
function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        errorAlert.textContent = message;
        errorAlert.classList.remove('d-none');
        errorAlert.scrollIntoView({ behavior: 'smooth' });
    } else {
        alert(message); // Fallback nếu không có errorAlert
    }
}

// Ẩn thông báo lỗi
function hideError() {
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        errorAlert.classList.add('d-none');
        errorAlert.textContent = '';
    }
}

/**
 * Xử lý lỗi tập trung và hiển thị thông báo.
 * @param {Error} error - Đối tượng lỗi
 * @param {string} message - Thông báo lỗi tùy chỉnh
 */
function handleError(error, message) {
    console.error('Error:', error);
    showError(message);
}

/**
 * Thực hiện đăng xuất và chuyển hướng đến trang login.
 */
async function logout() {
    try {
        await fetch('/server_diem_danh/api/logout.php', { method: 'POST' });
        window.location.href = '/server_diem_danh/public/login/login.html';
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi đăng xuất');
    }
}

export { fetchCsrfToken, showError, hideError, handleError, logout };