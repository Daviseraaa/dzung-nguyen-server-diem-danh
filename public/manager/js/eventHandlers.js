// public/manager/js/eventHandlers.js
import { loadManagerClasses, loadManagerClassDates, loadManagerAttendanceReport, submitManagerManualAttendance } from '/server_diem_danh/public/assets/js/attendanceFilter.js';
import { checkAttendanceManager, displayAttendanceResultManager, exportToExcelManager } from './classManagement.js';
import { logout, handleError } from '/server_diem_danh/public/assets/js/utils.js';
import { addEntry, collectEntries, showConfirmation, submitAttendance, resetForm } from './manualAttendance.js';

let classData = [];
let dateColumns = [];

/**
 * Thiết lập tất cả các sự kiện DOM cho dashboard manager.
 */
export async function setupEventListeners() {
    // Load danh sách lớp cho dropdown (tab điểm danh thủ công)
    try {
        const teacher_id = window.teacher_id;
        const classes = await loadManagerClasses(teacher_id);
        // Tab điểm danh thủ công
        const manualClassSelect = document.getElementById('manualClassSelect');
        if (manualClassSelect) {
            manualClassSelect.innerHTML = '<option value="">Chọn lớp</option>';
            classes.forEach(cls => {
                manualClassSelect.innerHTML += `<option value="${cls.class_id}">${cls.class_name}</option>`;
            });
        }
        // Tab thống kê chuyên cần
        const filterClassSelect = document.getElementById('filterClassSelect');
        if (filterClassSelect) {
            filterClassSelect.innerHTML = '<option value="">Chọn lớp</option>';
            classes.forEach(cls => {
                filterClassSelect.innerHTML += `<option value="${cls.class_id}">${cls.class_name}</option>`;
            });
        }
    } catch (error) {
        handleError(error, 'Không thể load danh sách lớp');
    }

    // Khi chọn class_id, load ngày học cho dropdown (tab điểm danh thủ công)
    const manualClassSelect = document.getElementById('manualClassSelect');
    if (manualClassSelect) {
        manualClassSelect.addEventListener('change', async (e) => {
            const class_id = e.target.value;
            const dateSelect = document.getElementById('manualDateSelect');
            if (class_id && dateSelect) {
                try {
                    const dates = await loadManagerClassDates(class_id);
                    dateSelect.innerHTML = '<option value="">Chọn ngày học</option>';
                    dates.forEach(date => {
                        dateSelect.innerHTML += `<option value="${date}">${date}</option>`;
                    });
                } catch (error) {
                    handleError(error, 'Không thể load ngày học');
                }
            }
        });
    }

    // Khi chọn class_id, load ngày học cho filter (tab thống kê chuyên cần)
    const filterClassSelect = document.getElementById('filterClassSelect');
    if (filterClassSelect) {
        filterClassSelect.addEventListener('change', async (e) => {
            const class_id = e.target.value;
            const filterDatesContainer = document.getElementById('filterDatesContainer');
            if (class_id && filterDatesContainer) {
                try {
                    const dates = await loadManagerClassDates(class_id);
                    filterDatesContainer.innerHTML = '';
                    dates.forEach(date => {
                        filterDatesContainer.innerHTML += `
                            <div class="form-check form-check-inline">
                                <input class="form-check-input filter-date-checkbox" type="checkbox" id="date_${date}" value="${date}" checked>
                                <label class="form-check-label" for="date_${date}">${date}</label>
                            </div>`;
                    });
                } catch (error) {
                    handleError(error, 'Không thể load ngày học');
                }
            }
        });
    }

    // Sự kiện cho tab thống kê chuyên cần (tab 1)
    const filterBtn = document.getElementById('filterAttendanceBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', async () => {
            const class_id = document.getElementById('filterClassSelect').value;
            const checkedDates = Array.from(document.querySelectorAll('.filter-date-checkbox:checked')).map(cb => cb.value);
            if (!class_id) {
                alert('Vui lòng chọn lớp');
                return;
            }
            try {
                classData = await checkAttendanceManager(class_id, checkedDates);
                dateColumns = checkedDates;
                displayAttendanceResultManager(classData, dateColumns);
            } catch (error) {}
        });
    }
    // Nút đặt lại
    const resetBtn = document.getElementById('resetAttendanceBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', async () => {
            const class_id = document.getElementById('filterClassSelect').value;
            const filterDatesContainer = document.getElementById('filterDatesContainer');
            if (!class_id) return;
            try {
                const allDates = await loadManagerClassDates(class_id);
                classData = await checkAttendanceManager(class_id, allDates);
                dateColumns = allDates;
                displayAttendanceResultManager(classData, dateColumns);
                // Đặt lại checkbox UI
                if (filterDatesContainer) {
                    filterDatesContainer.querySelectorAll('.filter-date-checkbox').forEach(cb => cb.checked = true);
                }
            } catch (error) {}
        });
    }
    // Nút xuất excel
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const classId = document.getElementById('filterClassSelect').value;
            exportToExcelManager(classData, dateColumns, classId);
        });
    }

    // Sự kiện cho tab "Ghi điểm danh thủ công"
    const addEntryBtn = document.getElementById('addEntryBtn');
    if (addEntryBtn) addEntryBtn.addEventListener('click', addEntry);
    const manualAttendanceForm = document.getElementById('manualAttendanceForm');
    if (manualAttendanceForm) manualAttendanceForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const entries = collectEntries();
        if (entries.length === 0) {
            alert('Vui lòng nhập ít nhất một dòng dữ liệu');
            return;
        }
        showConfirmation(entries);
        window.manualEntries = entries;
    });
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', async () => {
        await submitAttendance(window.manualEntries);
    });
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) cancelBtn.addEventListener('click', () => {
        document.getElementById('confirmationTable').style.display = 'none';
    });
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) logoutBtn.addEventListener('click', logout);
}