import { loadManagerAttendanceReport } from '/server_diem_danh/public/assets/js/attendanceFilter.js';
import { handleError } from '/server_diem_danh/public/assets/js/utils.js';
import { readExcelFile } from '/server_diem_danh/public/assets/js/excelUtils.js';

function createAttendanceRow(record, dateColumns) {
    let row = `<tr><td>${record.stt}</td><td>${record.full_name}</td><td>${record.student_id}</td>`;
    dateColumns.forEach(date => {
        let status = record.attendance[date] || '';
        let display = status === 'on_time' ? 'Đi học' : (status === 'late' ? 'Muộn' : 'Vắng');
        row += `<td>${display}</td>`;
    });
    row += `<td>${record.total_absent}</td><td>${record.total_on_time}</td><td>${record.total_late}</td></tr>`;
    return row;
}

// Hàm mới: kiểm tra chuyên cần dựa trên API manager
async function checkAttendanceManager(class_id, dates = []) {
    try {
        const attendanceRecords = await loadManagerAttendanceReport(class_id, dates);
        return attendanceRecords;
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi kiểm tra điểm danh');
        throw error;
    }
}

function renderAttendanceTableHead(dateColumns) {
    let head = '<tr>';
    head += '<th>STT</th><th>Họ và tên</th><th>MSSV</th>';
    dateColumns.forEach(date => {
        head += `<th>${date}</th>`;
    });
    head += '<th>Tổng vắng</th><th>Tổng đi học</th><th>Tổng muộn</th></tr>';
    return head;
}

function displayAttendanceResultManager(classData, dateColumns) {
    const attendanceList = document.getElementById('attendanceList');
    attendanceList.innerHTML = '';
    classData.forEach(record => {
        attendanceList.innerHTML += createAttendanceRow(record, dateColumns);
    });
    // Render thead
    const attendanceTableHead = document.getElementById('attendanceTableHead');
    if (attendanceTableHead) {
        attendanceTableHead.innerHTML = renderAttendanceTableHead(dateColumns);
    }
    document.getElementById('attendanceResult').style.display = 'block';
}

function exportToExcelManager(classData, dateColumns, classId) {
    try {
        if (!classData || classData.length === 0) {
            throw new Error('Không có dữ liệu để xuất file Excel');
        }
        const exportData = classData.map(item => {
            let row = {
                'STT': item.stt,
                'Họ và tên': item.full_name,
                'MSSV': item.student_id
            };
            dateColumns.forEach(date => {
                let status = item.attendance[date] || '';
                row[date] = status === 'on_time' ? 'Đi học' : (status === 'late' ? 'Muộn' : 'Vắng');
            });
            row['Tổng vắng'] = item.total_absent;
            row['Tổng đi học'] = item.total_on_time;
            row['Tổng muộn'] = item.total_late;
            return row;
        });
        const worksheet = XLSX.utils.json_to_sheet(exportData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'DiemDanh');
        const fileName = `chuyen_can_${classId || 'lop'}_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(workbook, fileName, { bookType: 'xlsx', type: 'binary' });
    } catch (error) {
        handleError(error, 'Đã có lỗi xảy ra khi xuất file Excel');
    }
}

export { checkAttendanceManager, displayAttendanceResultManager, exportToExcelManager };