# Quy trình Đăng nhập của Hệ thống Điểm danh

Dưới đây là mô tả chi tiết về luồng xử lý của module đăng nhập trong webapp "Hệ thống Điểm danh", được xây dựng dựa trên các file mã nguồn đã cung cấp (`login.js`, `check_session.php`, `login.php`, `logout.php`, `get_csrf_token.php`, `login.html`, `session.js`). Quy trình này bao gồm các bước từ khi người dùng truy cập trang đăng nhập đến khi hoàn tất đăng nhập hoặc đăng xuất.

---

## 1. Truy cập Trang Đăng nhập
- **Điểm bắt đầu**: Người dùng truy cập trang đăng nhập tại `/server_diem_danh/public/login/login.html`.
- **File liên quan**: `login.html`
- **Mô tả**:
  - Trang `login.html` được tải, hiển thị một form đăng nhập với các trường:
    - Tên đăng nhập (`username`)
    - Mật khẩu (`password`)
    - Trường ẩn chứa CSRF token (`csrf_token`)
  - Form sử dụng Bootstrap 5 để đảm bảo giao diện thân thiện và responsive.
  - Các script JavaScript được tải:
    - `utils.js` (chứa các hàm tiện ích như `showError`, `hideError`, `fetchCsrfToken`)
    - `session.js` (kiểm tra trạng thái session)
    - `login.js` (xử lý logic đăng nhập)

---

## 2. Kiểm tra Trạng thái Session
- **Thời điểm**: Khi trang `login.html` tải xong (`DOMContentLoaded`).
- **File liên quan**: `session.js`, `check_session.php`
- **Mô tả**:
  - Hàm `checkSession` trong `session.js` được gọi để kiểm tra xem người dùng đã đăng nhập hay chưa.
  - **Bước thực hiện**:
    1. Gửi yêu cầu GET tới `/server_diem_danh/api/check_session.php`.
    2. Server xử lý yêu cầu trong `check_session.php`:
       - Kiểm tra session bằng `Session::check()`.
       - Nếu session tồn tại (người dùng đã đăng nhập):
         - Trả về JSON với `logged_in: true` và `redirect` (URL dashboard tương ứng với vai trò: admin, manager, hoặc student).
       - Nếu không có session, trả về `logged_in: false`.
    3. Client xử lý phản hồi:
       - Nếu `logged_in: true`, chuyển hướng tới URL trong `redirect` (ví dụ: `/server_diem_danh/public/admin/dashboard.html` cho admin).
       - Nếu `logged_in: false`, tiếp tục quá trình đăng nhập.

---

## 3. Lấy CSRF Token
- **Thời điểm**: Ngay sau khi kiểm tra session (nếu người dùng chưa đăng nhập).
- **File liên quan**: `login.js`, `get_csrf_token.php`
- **Mô tả**:
  - Hàm `fetchCsrfToken` trong `utils.js` (được gọi từ `login.js`) gửi yêu cầu GET tới `/server_diem_danh/api/get_csrf_token.php`.
  - Server xử lý trong `get_csrf_token.php`:
    - Gọi `CSRF::generate()` để tạo một token CSRF ngẫu nhiên.
    - Trả về JSON chứa `token`.
  - Client cập nhật giá trị của trường ẩn `#csrfToken` trong form đăng nhập với token nhận được.
  - Nếu lấy token thất bại, hiển thị thông báo lỗi: "Không thể lấy CSRF token. Vui lòng tải lại trang."

---

## 4. Người dùng Nhập Thông tin và Gửi Form
- **Thời điểm**: Người dùng nhập `username`, `password`, và nhấn nút "Đăng nhập".
- **File liên quan**: `login.html`, `login.js`
- **Mô tả**:
  - Form `#loginForm` trong `login.html` kích hoạt sự kiện `submit`.
  - Hàm `handleLogin` trong `login.js` được gọi:
    1. Ngăn chặn hành vi submit mặc định (`e.preventDefault()`).
    2. Ẩn thông báo lỗi trước đó (nếu có) bằng `hideError`.
    3. Vô hiệu hóa nút đăng nhập (`loginBtn.disabled = true`) và hiển thị spinner (`loadingSpinner`).
    4. Thu thập dữ liệu từ form:
       - `username`: Giá trị từ `#username`, đã được `trim()`.
       - `password`: Giá trị từ `#password`.
       - `csrf_token`: Giá trị từ `#csrfToken`.
    5. Tạo `URLSearchParams` từ dữ liệu và gửi yêu cầu POST tới `/server_diem_danh/api/login.php`.

---

## 5. Xử lý Yêu cầu Đăng nhập trên Server
- **File liên quan**: `login.php`
- **Mô tả**:
  - Server thực hiện các bước kiểm tra và xác thực:
    1. **Kiểm tra session hiện tại**:
       - Nếu người dùng đã đăng nhập (`Session::check()` trả về `true`), trả về JSON với thông báo "User already logged in" và URL redirect.
    2. **Kiểm tra phương thức**:
       - Chỉ chấp nhận phương thức POST, nếu không trả về lỗi 405 ("Method Not Allowed").
    3. **Kiểm tra Content-Type**:
       - Chấp nhận `application/x-www-form-urlencoded` hoặc `application/json`, nếu không trả về lỗi 415 ("Unsupported Media Type").
    4. **Đọc dữ liệu đầu vào**:
       - Nếu là `x-www-form-urlencoded`, lấy từ `$_POST`.
       - Nếu là JSON, phân tích từ `php://input`.
    5. **Kiểm tra CSRF token**:
       - Gọi `CSRF::validate($input['csrf_token'])`.
       - Nếu token không hợp lệ, trả về lỗi 403 ("Invalid CSRF token").
    6. **Validate và sanitize input**:
       - Loại bỏ thẻ HTML và khoảng trắng từ `username` bằng `strip_tags` và `trim`.
       - Kiểm tra `username` và `password` không được rỗng, nếu rỗng trả về lỗi 400 ("Bad Request").
    7. **Kiểm tra rate limiting**:
       - Dùng `RateLimiter::check($ip)` để kiểm tra số lần thử đăng nhập từ IP của người dùng.
       - Nếu vượt quá giới hạn, trả về lỗi 429 ("Too Many Requests") với thông báo khóa tạm thời.
    8. **Xác thực người dùng**:
       - Gọi `Auth::login($username, $password)` để kiểm tra thông tin đăng nhập.
       - Nếu xác thực thất bại:
         - Tăng số lần thử bằng `RateLimiter::increment($ip)`.
         - Trả về lỗi 401 ("Unauthorized") với số lần thử còn lại.
       - Nếu xác thực thành công:
         - Lưu thông tin người dùng vào session bằng `Session::setUser($user)`.
         - Tái tạo CSRF token bằng `CSRF::regenerate()`.
         - Xóa giới hạn rate limiting bằng `RateLimiter::reset($ip)`.
         - Ghi log đăng nhập thành công bằng `Logger::log`.
         - Trả về JSON với thông báo "Login successful", vai trò (`role`), và URL redirect.

---

## 6. Xử lý Phản hồi từ Server
- **File liên quan**: `login.js`
- **Mô tả**:
  - Hàm `handleLogin` xử lý phản hồi từ `/server_diem_danh/api/login.php`:
    1. Nếu yêu cầu thất bại (`!response.ok`):
       - Kiểm tra mã lỗi:
         - **401 (Session expired)**: Chuyển hướng tới URL trong `data.redirect`.
         - **403 (Invalid CSRF token)**: Hiển thị thông báo "Lỗi bảo mật: CSRF token không hợp lệ. Vui lòng tải lại trang."
         - Lỗi khác: Hiển thị thông báo lỗi chung với chi tiết từ `data.error`.
    2. Nếu thành công:
       - Chuyển hướng tới URL trong `data.redirect` (dashboard tương ứng với vai trò).
    3. Trong mọi trường hợp, khôi phục trạng thái ban đầu:
       - Bật lại nút đăng nhập (`loginBtn.disabled = false`).
       - Ẩn spinner (`loadingSpinner.style.display = 'none'`).
  - Nếu có lỗi trong quá trình xử lý (catch block), hiển thị thông báo lỗi chi tiết và ghi log vào console.

---

## 7. Đăng xuất
- **Thời điểm**: Người dùng yêu cầu đăng xuất (thông qua API hoặc giao diện).
- **File liên quan**: `logout.php`
- **Mô tả**:
  - Yêu cầu được gửi tới `/server_diem_danh/api/logout.php`.
  - Server xử lý:
    1. Khởi động session bằng `Session::start()`.
    2. Hủy session bằng `Session::destroy()`.
    3. Ghi log đăng xuất bằng `Logger::log` với địa chỉ IP.
    4. Chuyển hướng người dùng về trang đăng nhập (`/server_diem_danh/public/login/login.html`) bằng `Response::redirect`.

---

## Lưu ý Quan trọng
- **Bảo mật**:
  - CSRF token được sử dụng để bảo vệ chống tấn công CSRF.
  - Rate limiting ngăn chặn brute-force attack.
  - Security headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`) được thiết lập trong `login.php` và `logout.php`.
- **Xử lý lỗi**:
  - Mọi lỗi đều được trả về dưới dạng JSON với mã trạng thái HTTP phù hợp (400, 401, 403, 429, v.v.).
  - Client hiển thị thông báo lỗi thân thiện với người dùng.
- **Hiệu suất**:
  - Sử dụng `async/await` để xử lý bất đồng bộ trong JavaScript.
  - Giảm thiểu thao tác DOM để tối ưu giao diện.

---

## Sơ đồ Luồng
```plaintext
Người dùng truy cập login.html
    ↓
Kiểm tra session (session.js → check_session.php)
    ↓ (Đã đăng nhập)
Chuyển hướng đến dashboard
    ↓ (Chưa đăng nhập)
Lấy CSRF token (login.js → get_csrf_token.php)
    ↓
Người dùng nhập form và submit
    ↓
Gửi POST tới login.php (login.js)
    ↓
Server xác thực:
- Kiểm tra session
- Kiểm tra CSRF token
- Kiểm tra rate limiting
- Xác thực username/password
    ↓ (Thất bại)
Trả về lỗi (401, 403, 429, v.v.)
    ↓ (Thành công)
Lưu session, tái tạo CSRF, trả về redirect URL
    ↓
Client chuyển hướng đến dashboard
```

---

## Đề xuất Tối ưu
- Thêm validation phía client trong `login.html` để kiểm tra input trước khi gửi.
- Cải thiện thông báo lỗi để thân thiện hơn (ví dụ: "Tài khoản bị khóa, thử lại sau 5 phút").
- Viết unit test cho các hàm quan trọng (`handleLogin`, `CSRF::validate`).
- Thêm tài liệu API cho các endpoint (`login.php`, `check_session.php`).