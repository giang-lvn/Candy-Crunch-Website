-- Script tạo bảng REFUND nếu chưa tồn tại
-- Chạy script này trong phpMyAdmin hoặc MySQL CLI

CREATE TABLE IF NOT EXISTS REFUND (
    RefundID VARCHAR(10) PRIMARY KEY,
    OrderID VARCHAR(20) NOT NULL,
    RefundDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    RefundReason TEXT,
    RefundDescription TEXT,
    RefundMethod VARCHAR(100),
    RefundImage VARCHAR(255),
    RefundStatus VARCHAR(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm index cho OrderID để tìm kiếm nhanh
CREATE INDEX IF NOT EXISTS idx_refund_orderid ON REFUND(OrderID);

-- Kiểm tra bảng đã tạo thành công
DESCRIBE REFUND;
