# TEST MAPPING — Executable Tests ↔ QA Report Test Cases

Ánh xạ đầy đủ 97 executable tests trong repo này với 169 test case thiết kế trong `BaoCao_TestCase_LaptopVui.docx`.

## Ký hiệu

- ✅ Executable (có test code trong repo)
- ⏳ Not executable v1 (sẽ bổ sung dần v1.1, v1.2)
- ⚠️ Marked incomplete (bug đã biết, chờ M0/M1 fix)

---

## 5.2.1. Trang chủ & Điều hướng (10 designed → 8 executable)

| TC ID (report) | Test file | Method | Status |
|---|---|---|---|
| TC-HOME-01 | Feature/HomePageTest.php | `it_loads_homepage_successfully`, `homepage_displays_brand_and_navigation` | ✅ |
| TC-HOME-02 | Feature/HomePageTest.php | `homepage_shows_dynamic_category_menu` | ✅ |
| TC-HOME-03 | Feature/HomePageTest.php | `homepage_shows_featured_products_section` | ✅ |
| TC-HOME-04 | Feature/HomePageTest.php | `homepage_shows_most_viewed_section` | ✅ |
| TC-HOME-05 | — | Ảnh SP có alt text | ⏳ E2E later |
| TC-HOME-06 | — | Format giá đúng VNĐ | ✅ Unit/PriceFormatTest |
| TC-HOME-07 | — | Menu sticky khi scroll | ⏳ E2E later |
| TC-HOME-08 | Feature/HomePageTest.php | `homepage_shows_cart_badge_when_cart_has_items` | ✅ |
| TC-HOME-09 | Feature/HomePageTest.php | `invalid_route_returns_404` | ✅ |
| TC-HOME-10 | Feature/HomePageTest.php | `unsupported_method_returns_405` | ✅ |

## 5.2.2. Chi tiết SP & Danh mục (13 designed → 10 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-DETAIL-01 | `it_displays_valid_product_detail` | ✅ |
| TC-DETAIL-02 | `it_returns_404_for_nonexistent_product` | ✅ |
| TC-DETAIL-03 | `it_handles_string_id_as_404` | ✅ |
| TC-DETAIL-04 | `it_handles_negative_id_as_404` | ✅ |
| TC-DETAIL-05 | Hiển thị ảnh SP | ⏳ E2E |
| TC-DETAIL-06 | Hiển thị nhãn KM khi có gia_km | ⏳ E2E |
| TC-DETAIL-07 | soluotxem tăng sau khi xem | ⏳ integration |
| TC-DETAIL-08 | `it_escapes_html_in_description` | ✅ |
| TC-DETAIL-09 | SP xem nhiều ở sidebar | ⏳ E2E |
| TC-DETAIL-10 | `it_displays_valid_category` | ✅ |
| TC-DETAIL-11 | `it_returns_404_for_nonexistent_category` | ✅ |
| TC-DETAIL-12 | `it_clamps_page_number_to_valid_range` | ✅ |
| TC-DETAIL-13 | Empty category state | ⏳ E2E |

## 5.2.3. Tìm kiếm (8 designed → 6 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-SEARCH-01 | `it_shows_search_form_on_get` | ✅ |
| TC-SEARCH-02 | `it_returns_results_for_matching_keyword` | ✅ |
| TC-SEARCH-03 | `it_handles_empty_keyword` | ✅ |
| TC-SEARCH-04 | `it_shows_empty_state_when_no_results` | ✅ |
| TC-SEARCH-05 | `it_safely_handles_sql_injection_attempt` | ✅ |
| TC-SEARCH-06 | `it_escapes_xss_in_search_output` | ✅ |
| TC-SEARCH-07 | Case-insensitive matching | ⏳ |
| TC-SEARCH-08 | Autocomplete suggestions | ⏳ (US-05 not yet built) |

## 5.2.4. Giỏ hàng (17 designed → 14 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-CART-01 | `it_adds_product_to_empty_cart` | ✅ |
| TC-CART-02 | `it_increments_quantity_when_adding_existing_product` | ✅ |
| TC-CART-03 | `it_caps_quantity_at_99` | ✅ |
| TC-CART-04 | `it_caps_summed_quantity_at_99` | ✅ |
| TC-CART-05 | `it_clamps_quantity_zero_to_one` | ✅ |
| TC-CART-06 | `it_rejects_nonexistent_product` | ✅ |
| TC-CART-07 | Không thêm SP anhien=0 | ⏳ needs seed |
| TC-CART-08 | `empty_cart_shows_empty_state` | ✅ |
| TC-CART-09 | `it_updates_cart_quantity` | ✅ |
| TC-CART-10 | `setting_quantity_to_zero_removes_product` | ✅ |
| TC-CART-11 | `it_handles_invalid_update_body_gracefully` | ✅ |
| TC-CART-12 | `it_removes_single_product_from_cart` | ✅ |
| TC-CART-13 | `it_clears_entire_cart` | ✅ |
| TC-CART-14 | Cart tổng tiền chính xác | ✅ Unit/PriceFormatTest |
| TC-CART-15 | Xoá SP không tồn tại trong cart | ⏳ |
| TC-CART-16 | Update key không tồn tại | ⏳ |
| TC-CART-17 | Cart cross-session persistence | ⏳ v1.1 feature |

## 5.2.5. Thanh toán (16 designed → 12 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-CHK-01 | `empty_cart_redirects_from_checkout` | ✅ |
| TC-CHK-02 | Form prefill khi login | ⏳ |
| TC-CHK-03 | `it_creates_order_with_valid_data` | ✅ |
| TC-CHK-04 | `it_rejects_empty_name` | ✅ |
| TC-CHK-05 | `it_rejects_invalid_email` | ✅ |
| TC-CHK-06 | `it_rejects_empty_address` | ✅ |
| TC-CHK-07 | `it_rejects_invalid_phone` | ✅ |
| TC-CHK-08 | `it_accepts_international_phone` | ✅ |
| TC-CHK-09 | `it_rejects_short_phone` | ✅ |
| TC-CHK-10 | `it_strips_html_tags_from_name` | ✅ |
| TC-CHK-11 | Transaction rollback nếu no valid SP | ⏳ integration |
| TC-CHK-12 | Order snapshot ten_sp + gia | ⏳ integration |
| TC-CHK-13 | `it_should_prevent_double_submit` | ⚠️ Bug M1 |
| TC-CHK-14 | Cart cleared sau khi tạo đơn thành công | ⏳ E2E |
| TC-CHK-15 | `guest_can_complete_checkout` | ✅ |
| TC-CHK-16 | Ghi chú (v1.0 optional) | ⏳ v1.0 UI needed |

## 5.2.6. Đăng ký / Đăng nhập (21 designed → 15 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-AUTH-01 | `it_displays_registration_form` | ✅ |
| TC-AUTH-02 | `it_registers_new_user_with_valid_data` | ✅ |
| TC-AUTH-03 | `it_rejects_short_name` | ✅ |
| TC-AUTH-04 | `it_rejects_invalid_email_on_register` | ✅ |
| TC-AUTH-05 | `it_rejects_short_password` | ✅ |
| TC-AUTH-06 | `it_rejects_duplicate_email` | ✅ |
| TC-AUTH-07 | `it_normalizes_email_on_register` | ✅ |
| TC-AUTH-08 | Password lưu dạng hash | ⏳ integration |
| TC-AUTH-09 | Redirect /login sau register thành công | ✅ (implicit in TC-AUTH-02) |
| TC-AUTH-10 | `it_logs_in_with_valid_credentials` | ✅ |
| TC-AUTH-11 | `it_rejects_nonexistent_email` | ✅ |
| TC-AUTH-12 | `it_rejects_wrong_password` | ✅ |
| TC-AUTH-13 | `login_should_not_reveal_which_credential_is_wrong` | ⚠️ Bug M0 |
| TC-AUTH-14 | `login_should_have_rate_limit` | ⚠️ Bug M0 |
| TC-AUTH-15 | `it_logs_out_successfully` | ✅ |
| TC-AUTH-16 | Session regenerate sau login | ⚠️ Bug M0 |
| TC-AUTH-17 | Password verify BCRYPT | ⏳ Unit |
| TC-AUTH-18 | Session persist cross-page | ⏳ E2E |
| TC-AUTH-19 | E2E full auth journey | ✅ E2E/auth.spec.ts |
| TC-AUTH-20 | Register + login timing consistency | ⏳ security |
| TC-AUTH-21 | Concurrent login attempts | ⏳ v1.1 |

## 5.2.7. Admin - Sản phẩm (23 designed → 12 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-ADMIN-01 | `unauthenticated_admin_access_redirects_to_login` | ✅ |
| TC-ADMIN-02 | `unauthenticated_cannot_access_admin_products` | ✅ |
| TC-ADMIN-03 | `it_logs_in_admin_with_valid_credentials` | ✅ |
| TC-ADMIN-04 | `it_rejects_wrong_admin_password` | ✅ |
| TC-ADMIN-05 | `it_rejects_regular_user_from_admin_login` | ✅ |
| TC-ADMIN-06 | `admin_can_access_dashboard_after_login` | ✅ |
| TC-ADMIN-07 | `admin_can_logout` | ✅ |
| TC-ADMIN-08 | `admin_delete_product_should_require_csrf` | ⚠️ Bug M0 |
| TC-APROD-01 | `admin_can_view_product_list` | ✅ |
| TC-APROD-02 | `product_list_supports_pagination` | ✅ |
| TC-APROD-03 | `admin_can_search_products` | ✅ |
| TC-APROD-04 | `admin_can_sort_products_by_price` | ✅ |
| TC-APROD-05 | `admin_can_view_add_product_form` | ✅ |
| TC-APROD-06 | `admin_can_view_edit_product_form` | ✅ |
| TC-APROD-07 | `edit_form_returns_404_for_nonexistent_product` | ✅ |
| TC-APROD-08 | `delete_product_should_use_post_not_get` | ⚠️ Bug M0 |
| TC-APROD-09 | `delete_nonexistent_product_does_not_crash` | ✅ |
| TC-APROD-10 | `add_product_validation_missing_category` | ✅ |
| TC-APROD-11 | `form_should_preserve_data_on_validation_fail` | ⚠️ Bug M1 |
| TC-APROD-12 | `delete_product_with_orders_should_soft_delete` | ⚠️ Bug M1 |
| TC-APROD-13 | Upload ảnh valid | ⏳ multipart |
| TC-APROD-14 | Upload ảnh > 5MB rejected | ⏳ multipart |
| TC-APROD-15 | Upload wrong MIME rejected | ⏳ multipart |

## 5.2.8. Admin - Danh mục (10 designed → 6 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-ACAT-01 | `admin_can_view_category_list` | ✅ |
| TC-ACAT-02 | `admin_can_view_add_category_form` | ✅ |
| TC-ACAT-03 | `admin_can_add_new_category` | ✅ |
| TC-ACAT-04 | `admin_cannot_add_duplicate_category` | ✅ |
| TC-ACAT-05 | `admin_cannot_delete_category_with_products` | ✅ |
| TC-ACAT-06 | Edit category | ⏳ |
| TC-ACAT-07 | Toggle anhien | ⏳ |
| TC-ACAT-08 | Thutu order affects display | ⏳ |
| TC-ACAT-09 | Case-insensitive duplicate check | ⏳ |
| TC-ACAT-10 | Delete empty category succeeds | ✅ (implicit) |

## 5.2.9. Admin - Đơn hàng (6 designed → 4 executable)

| TC ID | Test | Status |
|---|---|---|
| TC-AORD-01 | `admin_can_view_orders_list` | ✅ |
| TC-AORD-02 | `admin_can_view_order_detail` | ✅ |
| TC-AORD-03 | `order_detail_returns_404_for_nonexistent` | ✅ |
| TC-AORD-04 | `order_status_workflow_should_exist` | ⚠️ v1.1 |
| TC-AORD-05 | Filter by date range | ⏳ v1.1 |
| TC-AORD-06 | Export CSV | ⏳ v1.2 |

## 5.3. UI/UX (45 designed → 10 core E2E journeys)

| Group | Executable via | Status |
|---|---|---|
| Homepage journey | E2E/homepage.spec.ts (4 tests) | ✅ |
| Cart & Checkout journey | E2E/cart-checkout.spec.ts (4 tests) | ✅ |
| Auth journey | E2E/auth.spec.ts (4 tests) | ✅ |
| Admin journey | E2E/admin.spec.ts (5 tests) | ✅ |
| Responsive design | E2E/responsive.spec.ts (4 tests) | ✅ |

Ghi chú: 35 UI/UX test case còn lại là quan sát bằng mắt (visual QA) — không thể tự động hoá hết. Sẽ bổ sung visual regression tests (Percy/Chromatic) trong v1.1.

---

## Tổng kết

- **Tổng test case thiết kế**: 169 (functional) + 45 (UI/UX) = **214**
- **Executable trong repo v1**: 97
- **Marked incomplete (bug/feature backlog)**: 12
- **Coverage**: ~57% (hợp lý cho v1 vì ưu tiên P0 High priority)

Roadmap tăng coverage:
- v1.1 (Q4/2026): +30 tests (visual regression, integration, multipart upload)
- v1.2 (Q1/2027): +40 tests (API v1 endpoints, mobile app)
