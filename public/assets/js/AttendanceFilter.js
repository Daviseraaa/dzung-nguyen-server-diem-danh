// public/assets/js/attendanceFilter.js

// ==== HÀM DÀNH RIÊNG CHO MANAGER ====

// Lấy danh sách lớp mà manager (teacher) quản lý
async function loadManagerClasses(teacher_id) {
    const response = await fetch(`/server_diem_danh/api/manager/get_classes_by_teacher.php?teacher_id=${teacher_id}`);
    if (!response.ok) throw new Error('Không thể load danh sách lớp');
    return await response.json();
}

// Lấy danh sách ngày học của một lớp
async function loadManagerClassDates(class_id) {
    const response = await fetch(`/server_diem_danh/api/manager/get_class_dates.php?class_id=${class_id}`);
    if (!response.ok) throw new Error('Không thể load ngày học');
    return await response.json();
}

// Lấy báo cáo chuyên cần của một lớp (có thể truyền mảng ngày học)
async function loadManagerAttendanceReport(class_id, dates = []) {
    let url = `/server_diem_danh/api/manager/get_class_attendance_report.php?class_id=${class_id}`;
    if (dates.length > 0) {
        dates.forEach(date => {
            url += `&dates[]=${encodeURIComponent(date)}`;
        });
    }
    const response = await fetch(url);
    if (!response.ok) throw new Error('Không thể load báo cáo chuyên cần');
    return await response.json();
}

// Ghi điểm danh thủ công cho manager
async function submitManagerManualAttendance(class_id, date, students) {
    const response = await fetch('/server_diem_danh/api/manager/manual_attendance_submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ class_id, date, students })
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Không thể ghi điểm danh');
    return data;
}

// Hiển thị/ẩn bộ lọc thời gian dựa trên ngày (nếu cần giữ lại cho UI)
function setupTimeFilter(dateInputId, timeFilterId, startTimeId, endTimeId) {
    const dateInput = document.getElementById(dateInputId);
    const timeFilter = document.getElementById(timeFilterId);
    const startTimeInput = document.getElementById(startTimeId);
    const endTimeInput = document.getElementById(endTimeId);

    dateInput.addEventListener('change', (e) => {
        timeFilter.style.display = e.target.value ? 'block' : 'none';
        if (!e.target.value) {
            startTimeInput.value = '';
            endTimeInput.value = '';
        }
    });
}

export {
    loadManagerClasses, loadManagerClassDates, loadManagerAttendanceReport, submitManagerManualAttendance, setupTimeFilter
};