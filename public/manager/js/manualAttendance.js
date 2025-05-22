// public/manager/js/manualAttendance.js
import { handleError } from '/server_diem_danh/public/assets/js/utils.js';
import { submitManagerManualAttendance } from '/server_diem_danh/public/assets/js/attendanceFilter.js';

const DOM = {
    attendanceEntries: document.getElementById('attendanceEntries'),
    confirmationTable: document.getElementById('confirmationTable'),
    confirmationList: document.getElementById('confirmationList'),
    classSelect: document.getElementById('manualClassSelect'), // cần có trong UI
    dateSelect: document.getElementById('manualDateSelect') // cần có trong UI
};

let entries = [];

export function addEntry() {
    const entryDiv = document.createElement('div');
    entryDiv.classList.add('row', 'mb-2', 'attendance-entry-row');
    entryDiv.innerHTML = `
        <div class="col-8">
            <input type="text" class="form-control" placeholder="Mã sinh viên (vd: sv001)" required>
        </div>
        <div class="col-4 d-flex align-items-center">
            <button type="button" class="btn btn-danger btn-sm remove-entry-btn ms-2">Xoá</button>
        </div>
    `;
    // Gắn sự kiện xoá dòng
    entryDiv.querySelector('.remove-entry-btn').addEventListener('click', function() {
        if (DOM.attendanceEntries.childElementCount > 1) {
            entryDiv.remove();
        }
    });
    DOM.attendanceEntries.appendChild(entryDiv);
}

export function collectEntries() {
    // Sử dụng Array.from để chắc chắn lấy đúng tất cả các dòng
    const entryDivs = Array.from(DOM.attendanceEntries.querySelectorAll('.attendance-entry-row'));
    const entries = [];
    entryDivs.forEach(div => {
        const studentIdInput = div.querySelector('input[placeholder="Mã sinh viên (vd: sv001)"]');
        if (studentIdInput && studentIdInput.value) {
            entries.push({
                student_id: studentIdInput.value.trim()
            });
        }
    });
    return entries;
}

export function showConfirmation(entries) {
    DOM.confirmationList.innerHTML = '';
    entries.forEach(entry => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${entry.student_id}</td>
        `;
        DOM.confirmationList.appendChild(row);
    });
    if (entries.length > 0) {
        DOM.confirmationTable.style.display = 'block';
        // Đảm bảo nút xác nhận luôn hiển thị
        let sendBtn = document.getElementById('sendConfirmBtn');
        if (!sendBtn) {
            sendBtn = document.createElement('button');
            sendBtn.id = 'sendConfirmBtn';
            sendBtn.className = 'btn btn-success me-2';
            sendBtn.textContent = 'Gửi';
            sendBtn.type = 'button';
            sendBtn.addEventListener('click', async () => {
                await submitAttendance(entries);
            });
            DOM.confirmationTable.appendChild(sendBtn);
        } else {
            sendBtn.style.display = '';
        }
    } else {
        DOM.confirmationTable.style.display = 'none';
    }
}

export async function submitAttendance(entries) {
    try {
        const class_id = DOM.classSelect.value;
        const date = DOM.dateSelect.value;
        if (!class_id || !date) {
            alert('Vui lòng chọn lớp và ngày học');
            return;
        }
        if (!entries || entries.length === 0) {
            alert('Vui lòng nhập ít nhất một dòng dữ liệu');
            return;
        }
        const response = await submitManagerManualAttendance(class_id, date, entries);
        alert(response.message);
        resetForm();
        DOM.confirmationTable.style.display = 'none';
        const sendBtn = document.getElementById('sendConfirmBtn');
        if (sendBtn) sendBtn.style.display = 'none';
    } catch (error) {
        // Xử lý lỗi thân thiện hơn
        if (error && error.message && error.message.includes('Không có sinh viên nào được điểm danh')) {
            alert('Tất cả sinh viên trong danh sách đã được điểm danh cho buổi học này. Không có dữ liệu mới được ghi nhận.');
            // Không reset form, cho phép người dùng chỉnh sửa và gửi lại
        } else {
            handleError(error, 'Đã có lỗi xảy ra khi ghi điểm danh');
        }
    }
}

export function resetForm() {
    DOM.attendanceEntries.innerHTML = '';
    addEntry();
    DOM.confirmationTable.style.display = 'none';
    const sendBtn = document.getElementById('sendConfirmBtn');
    if (sendBtn) sendBtn.style.display = 'none';
}