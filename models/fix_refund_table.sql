-- Script để sửa bảng REFUND
-- Chạy script này trong phpMyAdmin

-- 1. Xóa FOREIGN KEY constraint (nếu có)
ALTER TABLE REFUND DROP FOREIGN KEY refund_ibfk_1;

-- 2. Thêm cột RefundMethod nếu chưa có
ALTER TABLE REFUND ADD COLUMN IF NOT EXISTS RefundMethod VARCHAR(100) AFTER RefundDescription;

-- 3. Kiểm tra cấu trúc bảng
DESCRIBE REFUND;

-- 4. Xem dữ liệu hiện có
SELECT * FROM REFUND;
