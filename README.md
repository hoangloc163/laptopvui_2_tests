# Laptop Vui — Executable Test Suite

> Bộ test case tự động hoá cho hệ thống bán hàng "Laptop Vui". Được convert từ 169 test case thiết kế trong báo cáo QA sang code executable, có thể chạy trên VSCode (local) và Render.com (CI/CD).

[![CI](https://github.com/YOUR_ORG/laptop-vui-tests/actions/workflows/tests.yml/badge.svg)](https://github.com/YOUR_ORG/laptop-vui-tests/actions)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-10.5-blue)](https://phpunit.de/)
[![Playwright](https://img.shields.io/badge/Playwright-1.44-green)](https://playwright.dev/)

## 📋 Nội dung

Bộ test có 3 tầng:

| Tầng | Framework | Số test | Chạy khi nào |
|---|---|---|---|
| **Unit** | PHPUnit | 15 | Mỗi commit — kiểm tra logic Model độc lập |
| **Feature** | PHPUnit + Guzzle | 60 | Mỗi commit — kiểm tra HTTP endpoints |
| **E2E** | Playwright | 22 | Mỗi PR + deploy — kiểm tra user journey đầy đủ |

**Tổng: ~97 executable tests** (convert từ 169 test case thiết kế trong báo cáo QA).

## 🚀 Quick Start (Local trên VSCode)

### Prerequisites

- PHP 8.4+ (với extension: pdo_sqlite, mbstring, fileinfo, gd)
- Node.js 20+
- Composer 2+
- Git

### Setup 1 lần

```bash
# 1. Clone repo test + repo app song song
git clone https://github.com/YOUR_ORG/laptop-vui-tests.git
git clone https://github.com/YOUR_ORG/banhang.git

# 2. Install PHP dependencies (PHPUnit, Guzzle, Faker)
cd laptop-vui-tests
composer install

# 3. Install Node dependencies + Playwright browsers
npm install
npx playwright install --with-deps chromium firefox webkit

# 4. Copy sample config
cp .env.example .env
# Chỉnh .env: APP_URL=http://localhost:8000
```

### Chạy tests

**Terminal 1 — start app dev server:**
```bash
cd ../banhang
php -S localhost:8000 -t . dev-router.php
```

**Terminal 2 — run tests:**
```bash
cd laptop-vui-tests

# Chạy tất cả PHPUnit tests
composer test

# Chỉ unit tests
composer test:unit

# Chỉ feature tests (HTTP)
composer test:feature

# Chạy Playwright E2E
npm run test:e2e

# Chạy Playwright headed (thấy browser)
npm run test:e2e:headed

# Chạy 1 file cụ thể
npx playwright test tests/E2E/checkout.spec.ts

# Chạy với UI mode (debug)
npm run test:e2e:ui
```

### VSCode tích hợp

Cài extensions:
- **PHP Unit Test Explorer** — chạy PHPUnit từ sidebar
- **Playwright Test for VSCode** — chạy Playwright từ sidebar, debug từng step

`.vscode/settings.json` đã có sẵn config.

## 🌐 Deploy + Test trên Render.com

### Bước 1: Deploy Laptop Vui app

```yaml
# render.yaml (trong repo banhang)
services:
  - type: web
    name: laptop-vui-web
    env: docker
    plan: free
    dockerfilePath: ./Dockerfile
    envVars:
      - key: APP_ENV
        value: production
```

App sẽ có URL dạng `https://laptop-vui-web.onrender.com`.

### Bước 2: Config test suite hit vào URL Render

```bash
# .env
APP_URL=https://laptop-vui-web.onrender.com
```

### Bước 3: Chạy Playwright target Render

```bash
APP_URL=https://laptop-vui-web.onrender.com npm run test:e2e
```

### Bước 4: CI/CD Automation

Đã có sẵn `.github/workflows/tests.yml`:
- Trigger: push vào `main`, hoặc mỗi PR
- Steps:
  1. Setup PHP 8.4 + Node 20
  2. Install dependencies
  3. Start PHP dev server as background
  4. Run PHPUnit
  5. Run Playwright (headless)
  6. Upload HTML report + screenshots on failure

## 📁 Cấu trúc thư mục

```
laptop-vui-tests/
├── README.md                          Tài liệu này
├── IMPROVEMENTS.md                    Các cải thiện từ nhận xét
├── composer.json                      PHP dependencies
├── package.json                       Node dependencies
├── phpunit.xml                        PHPUnit config
├── playwright.config.ts               Playwright config
├── .env.example                       Sample config
├── .github/
│   └── workflows/
│       └── tests.yml                  CI/CD
├── render.yaml                        Render.com deploy config
├── tests/
│   ├── Unit/                          PHPUnit unit tests
│   │   ├── ValidationTest.php         Test validation logic
│   │   └── PriceFormatTest.php        Test business helpers
│   ├── Feature/                       PHPUnit HTTP tests
│   │   ├── HomePageTest.php           GET /
│   │   ├── ProductDetailTest.php      GET /sp?id=
│   │   ├── CategoryTest.php           GET /loai
│   │   ├── SearchTest.php             POST /tk
│   │   ├── CartTest.php               Cart operations
│   │   ├── CheckoutTest.php           POST /checkout_
│   │   ├── AuthTest.php               Register/Login
│   │   ├── AdminAuthTest.php          Admin login flow
│   │   └── AdminProductTest.php       Admin CRUD
│   ├── E2E/                           Playwright end-to-end
│   │   ├── homepage.spec.ts
│   │   ├── product-detail.spec.ts
│   │   ├── search.spec.ts
│   │   ├── cart-checkout.spec.ts      Full purchase journey
│   │   ├── auth.spec.ts               Register + login
│   │   ├── admin.spec.ts              Admin CRUD via UI
│   │   └── responsive.spec.ts         Mobile viewport
│   └── Support/
│       ├── TestCase.php               Base class
│       ├── HttpClient.php             Guzzle wrapper
│       └── DatabaseHelper.php         Seed/reset test DB
├── postman/
│   └── LaptopVui.postman_collection.json   Ready for API v1 testing
└── scripts/
    ├── setup.sh                       First-time setup script
    └── reset-test-db.php              Reset test database
```

## 🧪 Ánh xạ Test Plan → Executable Tests

Mỗi test case executable đều có comment tham chiếu đến ID test case trong báo cáo QA:

```php
/**
 * @testCase TC-HOME-01
 * @priority High
 * @source BaoCao_TestCase_LaptopVui.docx mục 5.2.1
 */
public function test_homepage_displays_all_five_sections(): void
{
    // ...
}
```

Xem file `TEST_MAPPING.md` để có bảng ánh xạ đầy đủ 97 executable tests ↔ 169 test cases trong report.

## 🐛 Debugging

**PHPUnit fail:**
```bash
composer test -- --debug --verbose
```

**Playwright fail:**
```bash
npx playwright test --debug              # Step through
npx playwright test --trace on           # Record trace
npx playwright show-trace trace.zip      # View trace
npx playwright show-report               # HTML report
```

**Common issues:**
- `Connection refused` → PHP server chưa start ở port 8000
- `Database is locked` → SQLite bị lock, delete `data/demo.sqlite` và restart server
- `Session expired` → CSRF token stale, restart PHP server

## 📊 Report

Sau khi chạy tests, các report được sinh ra:

- `coverage/` — PHPUnit code coverage (HTML)
- `playwright-report/` — Playwright HTML report với screenshots
- `test-results/` — Playwright artifacts (traces, videos)

CI/CD sẽ tự động upload các report này để download.

## 🤝 Contributing

Khi thêm test case mới:

1. Tìm test case ID trong báo cáo QA (VD: TC-CART-15)
2. Chọn tầng phù hợp (Unit / Feature / E2E)
3. Copy pattern từ file cùng nhóm
4. Comment `@testCase TC-XXX-NN` đầu method
5. Update `TEST_MAPPING.md`
6. Chạy `composer test` + `npm run test:e2e` để verify

## 📄 License

MIT — xem `LICENSE`.

## 🔗 Related

- **App source**: [github.com/YOUR_ORG/banhang](https://github.com/YOUR_ORG/banhang)
- **QA Report**: `docs/BaoCao_TestCase_LaptopVui.docx` (169 test case thiết kế)
- **PRD v1.0**: `docs/PRD_v1.0_LaptopVui.docx`
- **Improvements**: [IMPROVEMENTS.md](./IMPROVEMENTS.md)
