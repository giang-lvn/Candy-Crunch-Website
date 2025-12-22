-- SQL Script để cập nhật database cho tính năng hủy/trả hàng
-- Chạy script này trong phpMyAdmin hoặc MySQL CLI

-- Thêm cột PreviousStatus vào bảng CANCELLATION nếu chưa có
ALTER TABLE CANCELLATION 
ADD COLUMN IF NOT EXISTS PreviousStatus VARCHAR(50) DEFAULT 'Pending' AFTER CancellationStatus;

-- Thêm cột CancellationID nếu chưa có (làm primary key)
-- Trước tiên kiểm tra và xóa primary key cũ nếu có
-- ALTER TABLE CANCELLATION DROP PRIMARY KEY;
-- ALTER TABLE CANCELLATION ADD COLUMN CancellationID VARCHAR(10) PRIMARY KEY FIRST;

-- Tạo bảng CANCELLATION nếu chưa tồn tại
CREATE TABLE IF NOT EXISTS CANCELLATION (
    CancellationID VARCHAR(10) PRIMARY KEY,
    OrderID VARCHAR(20) NOT NULL,
    CancellationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CancellationReason TEXT,
    CancellationStatus VARCHAR(20) DEFAULT 'Pending',
    PreviousStatus VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY (OrderID) REFERENCES ORDERS(OrderID) ON DELETE CASCADE
);

-- Tạo bảng REFUND nếu chưa tồn tại (cho Return)
CREATE TABLE IF NOT EXISTS REFUND (
    RefundID VARCHAR(10) PRIMARY KEY,
    OrderID VARCHAR(20) NOT NULL,
    RefundDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    RefundReason TEXT,
    RefundDescription TEXT,
    RefundImage VARCHAR(255),
    RefundStatus VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (OrderID) REFERENCES ORDERS(OrderID) ON DELETE CASCADE
);

-- Thêm index để tăng tốc truy vấn
CREATE INDEX IF NOT EXISTS idx_cancellation_status ON CANCELLATION(CancellationStatus);
CREATE INDEX IF NOT EXISTS idx_cancellation_order ON CANCELLATION(OrderID);
CREATE INDEX IF NOT EXISTS idx_refund_status ON REFUND(RefundStatus);
CREATE INDEX IF NOT EXISTS idx_refund_order ON REFUND(OrderID);
