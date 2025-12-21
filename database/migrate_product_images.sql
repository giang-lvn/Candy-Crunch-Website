-- Migration: Chuyển cột Image từ bảng SKU sang bảng PRODUCT
-- Ngày tạo: 2024-12-21
-- Mục đích: Quản lý ảnh ở cấp độ sản phẩm thay vì SKU

-- Bước 1: Thêm cột Image vào bảng PRODUCT
ALTER TABLE PRODUCT ADD COLUMN Image TEXT;

-- Bước 2: Di chuyển dữ liệu ảnh từ SKU đầu tiên sang PRODUCT (tùy chọn)
-- Lưu ý: Chỉ chạy nếu bạn muốn giữ lại ảnh cũ
UPDATE PRODUCT p 
SET Image = (SELECT s.Image FROM SKU s WHERE s.ProductID = p.ProductID LIMIT 1)
WHERE p.Image IS NULL;

-- Bước 3: Xóa cột Image khỏi bảng SKU
-- QUAN TRỌNG: Chỉ chạy sau khi đã xác nhận dữ liệu đã được migrate thành công
-- ALTER TABLE SKU DROP COLUMN Image;
