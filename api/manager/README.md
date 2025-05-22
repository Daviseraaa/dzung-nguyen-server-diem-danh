# Thư mục api/manager

Thư mục này chứa các endpoint API phục vụ riêng cho vai trò manager (giáo viên) trong hệ thống điểm danh.

## Quy tắc phát triển API
- Mỗi file PHP là một endpoint, chỉ xử lý một chức năng duy nhất.
- Đặt tên file rõ nghĩa, sử dụng snake_case, mô tả đúng chức năng (ví dụ: get_class_attendance_report.php, manual_attendance_submit.php).
- Không viết logic xử lý phức tạp trực tiếp trong file API, nên tách ra module/helper nếu cần.

## Logging & Xử lý lỗi
- Sử dụng `Logger::log()` để ghi lại các lỗi, truy vấn thất bại, dữ liệu đầu vào không hợp lệ, hoặc các sự kiện quan trọng.
- Luôn trả về mã lỗi HTTP phù hợp (400, 405, 500, ...).
- Thông báo lỗi trả về cho client phải thân thiện, không lộ thông tin nhạy cảm.

## Comment & Code style
- Bổ sung comment cho từng khối xử lý chính: validate, truy vấn, xử lý logic, trả về kết quả.
- Đặt tên biến/hàm rõ nghĩa, nhất quán, ưu tiên tiếng Anh.
- Không thay đổi tên biến trả về cho client nếu chưa kiểm tra đồng bộ phía frontend.

## Best practices
- Đảm bảo mỗi endpoint chỉ phục vụ đúng vai trò manager, không lẫn lộn với admin/student.
- Đóng kết nối DB sau khi xử lý xong.
- Nếu cần mở rộng, nên tách các hàm tiện ích ra module riêng trong `/modules`.

## Ví dụ import Logger
```php
require_once '../../modules/Logger.php';
Logger::log("[endpoint_name] Thông báo lỗi hoặc sự kiện");
```

## Liên hệ
- Nếu phát hiện bug hoặc cần mở rộng API, hãy liên hệ trưởng nhóm backend hoặc tham khảo tài liệu dự án. 