// public/assets/js/filterUtils.js

/**
 * Kiểm tra tính hợp lệ của bộ lọc.
 * @param {string} date - Ngày lọc
 * @param {string} startTime - Thời gian bắt đầu
 * @param {string} endTime - Thời gian kết thúc
 * @param {string} room - Phòng học
 * @returns {boolean} - True nếu hợp lệ, false nếu không
 */
function validateFilters(date, startTime, endTime, room) {
    if (!date && (!startTime || !endTime) && !room) {
        alert('Vui lòng nhập ít nhất một tiêu chí lọc (ngày, khoảng thời gian, hoặc phòng học)');
        return false;
    }
    if (startTime && endTime) {
        const start = new Date(`1970-01-01T${startTime}:00`);
        const end = new Date(`1970-01-01T${endTime}:00`);
        if (start >= end) {
            alert('Thời gian bắt đầu phải nhỏ hơn thời gian kết thúc');
            return false;
        }
    }
    return true;
}

/**
 * Tạo đối tượng bộ lọc từ input.
 * @param {string} date - Ngày lọc
 * @param {string} startTime - Thời gian bắt đầu
 * @param {string} endTime - Thời gian kết thúc
 * @param {string} room - Phòng học
 * @returns {Object} - Đối tượng bộ lọc
 */
function createFilterObject(date, startTime, endTime, room) {
    const filters = {};
    if (date) filters.date = date;
    if (startTime && endTime) {
        filters.startTime = startTime;
        filters.endTime = endTime;
    }
    if (room) filters.room = room;
    return filters;
}

/**
 * Đặt lại các input bộ lọc.
 * @param {string[]} inputIds - ID của các input (date, startTime, endTime, room)
 * @param {string} timeFilterId - ID của div chứa bộ lọc thời gian
 */
function resetFilters(inputIds, timeFilterId) {
    inputIds.forEach(id => {
        document.getElementById(id).value = '';
    });
    if (timeFilterId) {
        document.getElementById(timeFilterId).style.display = 'none';
    }
}

export { validateFilters, createFilterObject, resetFilters };