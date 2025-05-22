// public/js/session.js
import { API_URLS } from './config.js';
import { showError } from './utils.js';

/**
 * Kiểm tra trạng thái session hiện tại.
 * @returns {Promise<boolean>} - True nếu đã đăng nhập, false nếu chưa
 */
export async function checkSession() {
    try {
        const response = await fetch(API_URLS.CHECK_SESSION, {
            method: 'GET'
        });
        const data = await response.json();
        
        if (data.logged_in) {
            window.location.href = data.redirect;
            return true;
        }
        return false;
    } catch (error) {
        showError('Không thể kiểm tra trạng thái đăng nhập. Vui lòng thử lại.');
        console.error('Session check failed:', error);
        return false;
    }
}