# Thư mục assets/js

Thư mục này chứa các file JavaScript tiện ích dùng chung cho toàn bộ hệ thống.

## Quy tắc đặt tên file
- Sử dụng camelCase cho tên file (ví dụ: utils.js, excelUtils.js, session.js).
- Mỗi file chỉ nên chứa các hàm phục vụ một nhóm chức năng cụ thể.
- Các file API nên có hậu tố `Api` (ví dụ: attendanceApi.js, classApi.js) nếu cần tách biệt rõ.

## Vai trò các file:
- `utils.js`: Hàm tiện ích chung (xử lý lỗi, CSRF, logout, hiển thị/ẩn lỗi).
- `excelUtils.js`: Hàm xuất file Excel.
- `session.js`: Xử lý session phía client.
- `filterUtils.js`: Hàm lọc, xử lý dữ liệu.
- `attendanceFilter.js`: Hàm gọi API lọc điểm danh.
- `config.js`: Biến cấu hình chung.
- `login.js`: Xử lý đăng nhập.

## Hướng dẫn sử dụng
- Import các hàm cần thiết bằng cú pháp ES6:
  ```js
  import { handleError } from '/server_diem_danh/public/assets/js/utils.js';
  ```
- Không nên viết logic đặc thù từng vai trò (manager, admin, student) trong thư mục này. 