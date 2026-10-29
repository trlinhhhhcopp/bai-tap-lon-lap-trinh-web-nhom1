
-- =====================================================
-- PHAN CONG THANH - ADMIN
-- Chuc nang: Quan ly ngay nghi le (holidays)
-- Database: helpdesk_thudo
-- =====================================================

USE helpdesk_thudo;

-- 1. Lay danh sach ngay nghi le
SELECT id, name, start_date, end_date, is_recurring, created_at
FROM holidays
ORDER BY start_date DESC;

-- 2. Tim kiem ngay nghi le theo ten
-- Thay 'Tet' bang tu khoa muon tim
SELECT id, name, start_date, end_date, is_recurring
FROM holidays
WHERE name LIKE '%Tet%'
ORDER BY start_date DESC;

-- 3. Dem tong so ngay nghi le
SELECT COUNT(*) AS total_holidays
FROM holidays;

-- 4. Xem chi tiet mot ngay nghi le theo ID
-- Thay 1 bang ID can xem
SELECT *
FROM holidays
WHERE id = 1;

-- 5. Phan trang: lay 10 ban ghi dau tien
SELECT id, name, start_date, end_date, is_recurring
FROM holidays
ORDER BY start_date DESC
LIMIT 10 OFFSET 0;




