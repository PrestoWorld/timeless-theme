# PLAN — Port UI `bookowl-timeless` vào theme WordPress `timeless` (Gutenberg block chuẩn, KHÔNG app.js)

**Thư mục theme:** `/Users/puleeno/Projects/bookowl.org/content/themes/timeless`  
**Nguồn tham chiếu (UI tuyệt đối):** `/Users/puleeno/Projects/bookowl-timeless/src` (React + Tailwind v4)  
**Ghi chú:** Giao diện dùng cho **BookPress**, không phải BookOwl. Chỉ tham khảo cấu trúc/HTML/CSS; render bằng Gutenberg block chuẩn.

## Mục tiêu
Các trang frontend trong theme `timeless` khớp UI với `bookowl-timeless`: màu `#E05A38`/`#1C1C1E`/`#FAF9F5`, font Be Vietnam Pro + Newsreader, navbar ribbon + thanh chính, flyout/mega menu, catalog bộ lọc, chi tiết sách, hướng dẫn thuê, máy tính cọc, tra cứu đơn, kho sách, ký gửi, thanh toán, giỏ hàng, thanh di chuyển mobile. Render bằng block Gutenberg (markup HTML + class Tailwind), không dùng app.js/SPA.

## Bước 1 — Môi trường build CSS (Đã làm)
- `assets/css/app.css`: `@import "tailwindcss"`, theme fonts + base
- `build-css.js`: PostCSS `@tailwindcss/postcss` + `autoprefixer` → `assets/css/dist/tailwind.css`
- `package.json`: script `build:css` (chạy thành công)
- 3 ảnh nguồn copy vào `assets/images/`

## Bước 2 — `functions.php` (Đã làm)
- `language_attributes` → `lang="vi"`
- `wp_head`: meta charset/viewport, preconnect font, `<title>`, `description`
- `wp_enqueue_scripts`: enqueue chỉ `tailwind.css` (bỏ `app.js`, bỏ query var `view`)

## Bước 3 — Block templates HTML (Gutenberg chuẩn, port markup/CSS từ React)
Viết markup block (`<!-- wp:... -->`) + class Tailwind port từ các component React:
- `parts/header.html` → Navbar (ribbon, thanh chính `#E05A38`, logo, flyout, tìm kiếm, giỏ, nút phải, menu mobile)
- `parts/footer.html` → Footer (brand, liên kết nhanh, cam kết, bản tin, bản quyền)
- `templates/home.html` → Trang chủ: HeroSection + BookCatalog + banner mô hình thư viện
- `templates/page.html`, `templates/single.html`, `templates/index.html`, `templates/404.html`, `templates/search.html`, `templates/archive.html` → khung block tương ứng
- Các trang con (catalog, book-detail, rental-guide, calculator, tracking, warehouses, consignment, checkout) → block template/page tuỳ ngữ cảnh (UI port từ React, interactivity giới hạn do block tĩnh)

## Bước 4 — Xác minh
- `npm run build:css`
- Kiểm tra markup block không bị lỗi
- Xác nhận/rebrand tên hiển thị CheepHub → BookPress