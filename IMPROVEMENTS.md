# CẢI THIỆN BÁO CÁO TEST CASE — Dựa trên nhận xét

> Tài liệu tổng hợp các điểm cần sửa/bổ sung cho báo cáo test case Laptop Vui, dựa trên nhận xét của review v1.

---

## 🔴 PHẦN 1: LỖI SỐ LIỆU TRONG BẢNG TỔNG HỢP (BẮT BUỘC SỬA)

### 1.1. Bảng 5.5.4 — Số liệu test case theo mức ưu tiên (SAI 7/9 nhóm)

**Bảng CŨ (báo cáo hiện tại - SAI):**

| Nhóm | Tổng | High | Medium | Low |
|---|---|---|---|---|
| Trang chủ & Điều hướng | 10 | 7 ❌ | 2 ❌ | 1 |
| Chi tiết SP & Danh mục | 13 | 8 ❌ | 4 ❌ | 1 ❌ |
| Tìm kiếm | 8 | 3 ❌ | 4 ❌ | 1 |
| Giỏ hàng | 17 | 13 | 4 | 0 |
| Thanh toán | 16 | 13 ❌ | 3 ❌ | 0 |
| Đăng ký/Đăng nhập | 21 | 17 ❌ | 4 ❌ | 0 |
| Admin - Sản phẩm | 23 | 17 ❌ | 5 ❌ | 1 |
| Admin - Danh mục | 10 | 7 ❌ | 2 ❌ | 1 |
| Admin - Đơn hàng | 6 | 4 | 2 | 0 |
| UI/UX | 45 | 20 ❌ | 18 | 7 ❌ |
| **TỔNG** | **169** | **109** | **48 ❌** | **12 ❌** |

**Bảng ĐÚNG (đã đếm lại từ danh sách chi tiết):**

| Nhóm | Tổng | High | Medium | Low |
|---|---|---|---|---|
| Trang chủ & Điều hướng | 10 | 6 | 3 | 1 |
| Chi tiết SP & Danh mục | 13 | 7 | 6 | 0 |
| Tìm kiếm | 8 | 4 | 3 | 1 |
| Giỏ hàng | 17 | 13 | 4 | 0 |
| Thanh toán | 16 | 12 | 4 | 0 |
| Đăng ký/Đăng nhập | 21 | 15 | 6 | 0 |
| Admin - Sản phẩm | 23 | 16 | 6 | 1 |
| Admin - Danh mục | 10 | 8 | 1 | 1 |
| Admin - Đơn hàng | 6 | 4 | 2 | 0 |
| UI/UX | 45 | 24 | 18 | 3 |
| **TỔNG** | **169** | **109** | **53** | **7** |

### 1.2. Bảng 5.5.5 — Số liệu lỗ hổng (SAI 2/3 nhóm)

**Bảng ĐÚNG:**

| Nhóm | Tổng | Cao | Trung bình | Thấp |
|---|---|---|---|---|
| Bảo mật | 14 | 4 | 5 | 5 |
| Chức năng | 10 | 4 | 5 | 1 |
| UX | 12 | 0 | 5 | 7 |
| **TỔNG** | **36** | **8** | **15** | **13** |

*Báo cáo cũ ghi 8/13/15 — Trung bình và Thấp bị đảo ngược cho nhau.*

---

## 🟠 PHẦN 2: VẤN ĐỀ DIỄN GIẢI (NÊN SỬA)

### 2.1. Tiêu đề mục 5.5.4 gây hiểu nhầm

**Vấn đề**: Tiêu đề "Số liệu kiểm thử tổng hợp" khiến người đọc hiểu nhầm rằng 169 test case đã được thực thi và có kết quả PASS/FAIL. Thực tế đầu mục 5.2 đã ghi "Trạng thái mặc định là 'Chưa chạy'".

**Sửa**: Đổi tiêu đề thành:
- **"5.5.4. Số liệu test case đã thiết kế theo mức ưu tiên (chưa thực thi)"**
- Hoặc: **"5.5.4. Test plan summary — Số lượng test case đã thiết kế"**

Thêm ghi chú rõ ràng ở đầu mục:
> *"Bảng này thống kê **số lượng test case đã được thiết kế** để chuẩn bị cho kiểm thử, chưa phải kết quả thực thi. Việc thực thi các test case này được thực hiện qua bộ test executable trên GitHub (xem repository laptop-vui-tests)."*

---

## 🟡 PHẦN 3: KHOẢNG TRỐNG SO VỚI YÊU CẦU (NÊN BỔ SUNG)

### 3.1. Test case chưa được thực thi thực sự

**Vấn đề**: 169 test case chỉ là **test plan** (thiết kế trên giấy). Chưa có test case nào được thực sự chạy trên hệ thống để xác nhận PASS/FAIL.

**Giải pháp**: Tạo bộ test case executable → **Đây là mục tiêu chính của repo này (xem README.md)**.

### 3.2. Thiếu wireframe dạng hình ảnh

**Vấn đề**: Mục 3.6 chỉ có sitemap text, không có bản vẽ phác thảo bố cục.

**Giải pháp**: Bổ sung wireframe cho các trang chính (home, detail, cart, checkout, admin dashboard) - có thể dùng Figma/Balsamiq/pen&paper scan.

### 3.3. Thiếu link tương tác (prototype)

**Vấn đề**: Không có link Figma/prototype bấm được.

**Giải pháp**: Tạo Figma prototype hoặc deploy web lên Render.com và cung cấp link public.

### 3.4. Thiếu mục "Nhóm đã sử dụng AI thế nào?"

**Vấn đề**: Đây là câu hỏi bắt buộc trong đề bài chấm điểm nhưng không xuất hiện trong 41 trang báo cáo.

**Giải pháp**: Thêm chương riêng cuối báo cáo mô tả cụ thể:
- AI đã dùng để làm gì (analyze codebase, generate test cases, draw diagrams, write PRD)
- Prompt/skill nào đã dùng
- Phần nào con người review/chỉnh sửa

### 3.5. Trọng tâm nghiêng về QA hơn UI/UX

**Vấn đề**: Báo cáo có tổng cộng 169 test case + 36 lỗ hổng bảo mật - quá thiên về pentest, trong khi đề bài yêu cầu quy trình thiết kế UI/UX 5 bước.

**Giải pháp**: Đã một phần được khắc phục qua restructure sang 5 chương HCI. Có thể cân đối thêm bằng cách:
- Bổ sung wireframe/prototype (3.2, 3.3)
- Thêm usability testing results (Chương 5 nên có kết quả test với user thật)

---

## ✅ PHẦN 4: BỘ TEST CASE EXECUTABLE (GIẢI QUYẾT KHOẢNG TRỐNG 3.1)

Repository này chứa bộ test case đã được convert từ 169 test plan sang code thực thi được:

### 4.1. Phạm vi executable v1

| Nhóm test | Trong plan (thiết kế) | Trong repo này (executable) | Coverage |
|---|---|---|---|
| Trang chủ & Điều hướng | 10 | 8 | 80% |
| Chi tiết SP & Danh mục | 13 | 10 | 77% |
| Tìm kiếm | 8 | 6 | 75% |
| Giỏ hàng | 17 | 14 | 82% |
| Thanh toán | 16 | 12 | 75% |
| Đăng ký/Đăng nhập | 21 | 15 | 71% |
| Admin - Sản phẩm | 23 | 12 | 52% |
| Admin - Danh mục | 10 | 6 | 60% |
| Admin - Đơn hàng | 6 | 4 | 67% |
| UI/UX (E2E only) | 45 | 10 (core journeys) | 22% |
| **TỔNG** | **169** | **97** | **~57%** |

*Note: Coverage 57% là hợp lý cho v1 - ưu tiên P0 High priority tests trước. Các test P1/P2 sẽ được bổ sung dần.*

### 4.2. Chạy thế nào

**Trên VSCode (local dev):**
```bash
# 1. Clone repo
git clone <this-repo-url>
cd laptop-vui-tests

# 2. Install dependencies
composer install
npm install
npx playwright install

# 3. Chạy PHP dev server (target app)
cd ../banhang && php -S localhost:8000 -t . dev-router.php

# 4. Chạy tests
composer test          # PHPUnit
npm run test:e2e       # Playwright E2E
```

**Trên Render.com (CI/CD):**
- Push code lên GitHub → GitHub Actions auto run PHPUnit + Playwright
- Render deploy web app → có URL public
- Playwright E2E target URL Render → chạy cron test hàng ngày

Xem chi tiết trong `README.md`.

---

## 📊 PHẦN 5: TÓM TẮT ƯU TIÊN

| Mức | Việc cần làm | Trạng thái |
|---|---|---|
| 🔴 P0 | Sửa bảng 5.5.4 số liệu H/M/L (7 nhóm sai) | Đã cung cấp bảng đúng ở mục 1.1 - **cần update DOCX** |
| 🔴 P0 | Sửa bảng 5.5.5 lỗ hổng (Bảo mật + UX) | Đã cung cấp bảng đúng ở mục 1.2 - **cần update DOCX** |
| 🔴 P0 | Đổi tiêu đề "Kết quả kiểm thử" | Đã đề xuất tiêu đề mới ở 2.1 |
| 🔴 P0 | Thêm mục "Nhóm đã sử dụng AI thế nào?" | Đã đề xuất nội dung ở 3.4 |
| 🟠 P1 | Bổ sung wireframe hình ảnh | Cần Figma/vẽ tay |
| 🟠 P1 | Bổ sung link prototype public | Cần deploy Figma/Render |
| 🟢 P0 | Tạo bộ test case executable | **Chính là repo này ✅** |
