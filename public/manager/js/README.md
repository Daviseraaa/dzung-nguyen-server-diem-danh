# Thư mục manager/js

Thư mục này chứa các file JavaScript phục vụ riêng cho dashboard của vai trò manager (giáo viên).

## Quy tắc đặt tên file
- Sử dụng camelCase cho tên file (manualAttendance.js, classManagement.js, eventHandlers.js).
- Mỗi file chỉ nên xử lý một nhóm chức năng cụ thể.

## Vai trò các file:
- `manualAttendance.js`: Xử lý logic điểm danh thủ công, xác nhận, gửi dữ liệu.
- `classManagement.js`: Quản lý danh sách lớp, lọc lớp, thao tác với lớp học.
- `eventHandlers.js`: Đăng ký và xử lý các sự kiện UI trên dashboard manager.
- `managerDashboard.js`: Khởi tạo, điều phối các thành phần chính của dashboard.
- `config.js`: Biến cấu hình riêng cho dashboard manager.

## Hướng dẫn sử dụng
- Import các hàm tiện ích chung từ `/public/assets/js/` khi cần.
- Không nên viết hàm tiện ích dùng chung ở đây, hãy đưa vào assets/js. 