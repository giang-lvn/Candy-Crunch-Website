-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 24, 2025 lúc 11:12 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `candy_crunch`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
--

CREATE TABLE `account` (
  `AccountID` varchar(10) NOT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `AccountStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`AccountID`, `Email`, `Password`, `AccountStatus`) VALUES
('ACC001', 'gianghihi@gmail.com', '$2y$10$2xC0rOiJ7XMTPpl/CvuiauEyYCYYZtNRFPg6u6eja8dtovqE6EARC', 'Banned'),
('ACC002', 'gianghihihi@gmail.com', '$2y$10$G/ltNAAb3UUOSH7kUvmMquMU9Y79NL2B3N.mRTtXG8lXsGplsKG16', 'Active'),
('ACC003', 'gianggiang@gmail.com', '$2y$10$gav2oIA4Z5xucvam4adA8uRsC5JJXEeE1HNXUfXRG9UPmRjBaGBP2', 'Banned'),
('ACC004', 'giangtest@gmail.com', '$2y$10$jugqk9XAdAWy5UPbxj9m.e5SDQaMy4HfkcVwHIKdsWg.Ld0tB0Mxe', 'Active'),
('ACC005', 'ngocgiang@gmail.com', '$2y$10$bH/UUaEkutR5uVw9g34a0OBxf5Y0/Kwzwo4d7EAK.1wjtspjqWypa', 'Active'),
('ADMIN001', 'admin@example.com', '$2y$10$1uHelJL2dKQCSC6bKVMQE.O0/6jq2tkY19UkzGuBxFDw9h2orQ8x.', 'ACTIVE');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `address`
--

CREATE TABLE `address` (
  `AddressID` varchar(10) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `Fullname` varchar(150) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Alias` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CityState` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  `PostalCode` varchar(20) DEFAULT NULL,
  `AddressDefault` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `address`
--

INSERT INTO `address` (`AddressID`, `CustomerID`, `Fullname`, `Phone`, `Alias`, `Address`, `CityState`, `Country`, `PostalCode`, `AddressDefault`) VALUES
('ADD001', 'CUS001', 'Giang', '0399488488', '', '123 binh duong', 'nyc', 'usa', '70000', 'No'),
('ADD002', 'CUS002', 'Giang', '055582225', '', '123 Washington', 'NYC', 'USA', '100000', 'No'),
('ADD003', 'CUS003', 'Giang', '09998888', '', '123 pham the hien', 'ny', 'usa', '10000', 'No'),
('ADD004', 'CUS004', 'Giang', '0999383888', '', '123 street', 'nyc', 'usa', '70000', 'No'),
('ADD005', 'CUS005', 'Giang', '0998777666', '', '123 street', 'nyc', 'usa', '011111', 'No');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banking`
--

CREATE TABLE `banking` (
  `BankingID` varchar(10) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `IDNumber` varchar(20) DEFAULT NULL,
  `AccountNumber` varchar(20) DEFAULT NULL,
  `AccountHolderName` varchar(150) DEFAULT NULL,
  `BankName` varchar(150) DEFAULT NULL,
  `BankBranchName` varchar(150) DEFAULT NULL,
  `BankDefault` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banking`
--

INSERT INTO `banking` (`BankingID`, `CustomerID`, `IDNumber`, `AccountNumber`, `AccountHolderName`, `BankName`, `BankBranchName`, `BankDefault`) VALUES
('BAN001', 'CUS001', '1000', '10040', 'gianghihi', 'sgbank', 'sg', 'Yes'),
('BAN002', 'CUS002', '15555', '05555', 'GIANG', 'SG Banking', 'Saigon', 'Yes'),
('BAN003', 'CUS004', '1234567', '123456', 'GIANG', 'SG Bank', 'SG', 'Yes'),
('BAN004', 'CUS005', '12345678', '98888', 'GIANG', 'SG Bank', 'SG', 'Yes');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cancellation`
--

CREATE TABLE `cancellation` (
  `CancellationID` varchar(10) NOT NULL,
  `OrderID` varchar(10) DEFAULT NULL,
  `CancellationDate` datetime DEFAULT NULL,
  `CancellationReason` varchar(500) DEFAULT NULL,
  `CancellationStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `CartID` varchar(10) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `TimeUpdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`CartID`, `CustomerID`, `TimeUpdate`) VALUES
('CA001', 'CUS001', '2025-12-23 22:25:52'),
('CA002', 'CUS002', '2025-12-24 09:29:00'),
('CA003', 'CUS003', '2025-12-24 10:40:15'),
('CA004', 'CUS004', '2025-12-24 12:55:41'),
('CA005', 'CUS005', '2025-12-24 16:00:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_detail`
--

CREATE TABLE `cart_detail` (
  `CartID` varchar(10) NOT NULL,
  `SKUID` varchar(20) NOT NULL,
  `CartQuantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `CategoryID` varchar(10) NOT NULL,
  `CategoryName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`CategoryID`, `CategoryName`) VALUES
('CHG', 'Chewing Gum'),
('FHC', 'Filled-Hard Candy'),
('GUM', 'Gummy'),
('HAC', 'Hard Candy'),
('MAR', 'Marshmallow'),
('TEST001', 'Test'),
('TEST2', 'test 2'),
('TET', 'Tet Collection'),
('XMS', 'Christmas Collection');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer`
--

CREATE TABLE `customer` (
  `CustomerID` varchar(10) NOT NULL,
  `AccountID` varchar(10) DEFAULT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `CustomerBirth` date DEFAULT NULL,
  `CustomerGender` varchar(20) DEFAULT NULL,
  `Avatar` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customer`
--

INSERT INTO `customer` (`CustomerID`, `AccountID`, `FirstName`, `LastName`, `CustomerBirth`, `CustomerGender`, `Avatar`) VALUES
('CUS001', 'ACC001', 'giang', 'hihi', NULL, NULL, '/Candy-Crunch-Website/uploads/avatars/CUS001_1766504384.jpg'),
('CUS002', 'ACC002', 'giang', 'giang', NULL, NULL, NULL),
('CUS003', 'ACC003', 'Giang', 'hihi', NULL, NULL, NULL),
('CUS004', 'ACC004', 'Giang', 'hihi', NULL, NULL, NULL),
('CUS005', 'ACC005', 'Ngoc Gian', 'Le', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` varchar(10) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `SKUID` varchar(20) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Comment` varchar(500) DEFAULT NULL,
  `CreateDate` datetime DEFAULT NULL,
  `Status` enum('pending','approved','hidden') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `CustomerID`, `SKUID`, `Rating`, `Comment`, `CreateDate`, `Status`) VALUES
('FB001', 'CUS001', 'MAR-002-125', 3, 'quao', '2025-12-23 22:34:52', 'hidden'),
('FB002', 'CUS002', 'MAR-002-125', 3, 'ngon', '2025-12-24 09:58:43', 'approved'),
('FB003', 'CUS002', 'FHC-002-100', 3, 'cx đc', '2025-12-24 11:02:24', 'approved'),
('FB004', 'CUS002', 'LOL-001-15', 3, 'ngon dữ', '2025-12-24 12:34:25', 'approved'),
('FB005', 'CUS004', 'CHG-003-100', 4, 'ngon dữ chời', '2025-12-24 13:06:21', 'approved'),
('FB006', 'CUS005', 'LOL-001-15', 5, 'ngon dữ', '2025-12-24 16:05:23', 'approved');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory`
--

CREATE TABLE `inventory` (
  `InventoryID` varchar(10) NOT NULL,
  `Stock` int(11) DEFAULT NULL,
  `LastestUpdate` datetime DEFAULT NULL,
  `InventoryStatus` varchar(50) DEFAULT NULL
) ;

--
-- Đang đổ dữ liệu cho bảng `inventory`
--

INSERT INTO `inventory` (`InventoryID`, `Stock`, `LastestUpdate`, `InventoryStatus`) VALUES
('IVEN001', 52, '2025-11-12 00:00:00', 'Available'),
('IVEN002', 79, '2025-11-12 00:00:00', 'Available'),
('IVEN003', 32, '2025-11-12 00:00:00', 'Available'),
('IVEN004', 40, '2025-12-24 00:54:27', 'Available'),
('IVEN005', 48, '2025-11-12 00:00:00', 'Available'),
('IVEN006', 88, '2025-11-12 00:00:00', 'Available'),
('IVEN007', 64, '2025-12-24 00:43:12', 'Available'),
('IVEN008', 45, '2025-11-12 00:00:00', 'Available'),
('IVEN009', 45, '2025-11-12 00:00:00', 'Available'),
('IVEN010', 40, '2025-12-24 11:02:00', 'Available'),
('IVEN011', 16, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN012', 16, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN013', 98, '2025-11-12 00:00:00', 'Available'),
('IVEN014', 31, '2025-11-12 00:00:00', 'Available'),
('IVEN015', 71, '2025-11-12 00:00:00', 'Available'),
('IVEN016', 90, '2025-11-12 00:00:00', 'Available'),
('IVEN017', 13, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN018', 21, '2025-11-12 00:00:00', 'Available'),
('IVEN019', 87, '2025-12-24 00:14:23', 'Available'),
('IVEN020', 47, '2025-11-12 00:00:00', 'Available'),
('IVEN021', 15, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN022', 47, '2025-11-12 00:00:00', 'Available'),
('IVEN023', 72, '2025-11-12 00:00:00', 'Available'),
('IVEN024', 77, '2025-11-12 00:00:00', 'Available'),
('IVEN025', 22, '2025-12-24 13:04:57', 'Available'),
('IVEN026', 4, '2025-12-24 10:46:09', 'Low in stock'),
('IVEN027', 14, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN028', 16, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN029', 95, '2025-11-12 00:00:00', 'Available'),
('IVEN030', 53, '2025-11-12 00:00:00', 'Available'),
('IVEN031', 43, '2025-11-12 00:00:00', 'Available'),
('IVEN032', 62, '2025-11-12 00:00:00', 'Available'),
('IVEN033', 77, '2025-11-12 00:00:00', 'Available'),
('IVEN034', 72, '2025-12-24 09:33:38', 'Available'),
('IVEN035', 59, '2025-11-12 00:00:00', 'Available'),
('IVEN036', 25, '2025-11-12 00:00:00', 'Available'),
('IVEN037', 97, '2025-11-12 00:00:00', 'Available'),
('IVEN038', 97, '2025-11-12 00:00:00', 'Available'),
('IVEN039', 58, '2025-11-12 00:00:00', 'Available'),
('IVEN040', 69, '2025-12-24 00:20:31', 'Available'),
('IVEN041', 18, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN042', 99, '2025-11-12 00:00:00', 'Available'),
('IVEN043', 11, '2025-12-24 16:04:25', 'Low in stock'),
('IVEN044', 85, '2025-11-12 00:00:00', 'Available'),
('IVEN045', 71, '2025-11-12 00:00:00', 'Available'),
('IVEN046', 32, '2025-11-12 00:00:00', 'Available'),
('IVEN047', 46, '2025-11-12 00:00:00', 'Available'),
('IVEN048', 79, '2025-11-12 00:00:00', 'Available'),
('IVEN049', 95, '2025-11-12 00:00:00', 'Available'),
('IVEN050', 23, '2025-11-12 00:00:00', 'Available'),
('IVEN051', 85, '2025-11-12 00:00:00', 'Available'),
('IVEN052', 23, '2025-11-12 00:00:00', 'Available'),
('IVEN053', 97, '2025-11-12 00:00:00', 'Available'),
('IVEN054', 66, '2025-11-12 00:00:00', 'Available'),
('IVEN055', 90, '2025-11-12 00:00:00', 'Available'),
('IVEN056', 16, '2025-11-12 00:00:00', 'Low in stock'),
('IVEN057', 79, '2025-11-12 00:00:00', 'Available'),
('IVEN059', 100, NULL, 'Available'),
('IVEN060', 15, NULL, 'Low in stock');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `OrderID` varchar(10) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `VoucherID` varchar(10) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `ShippingMethod` varchar(50) DEFAULT NULL,
  `ShippingFee` decimal(10,2) DEFAULT NULL,
  `OrderStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerID`, `VoucherID`, `OrderDate`, `PaymentMethod`, `ShippingMethod`, `ShippingFee`, `OrderStatus`) VALUES
('ORD001', 'CUS001', NULL, '2025-12-23 16:26:42', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD002', 'CUS001', NULL, '2025-12-23 16:49:02', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD003', 'CUS001', NULL, '2025-12-23 17:12:02', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD004', 'CUS001', NULL, '2025-12-23 18:11:53', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD005', 'CUS001', NULL, '2025-12-23 18:14:23', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD006', 'CUS001', NULL, '2025-12-23 18:19:47', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD007', 'CUS001', NULL, '2025-12-23 18:20:31', 'COD', 'Standard', 0.00, 'Complete'),
('ORD008', 'CUS001', NULL, '2025-12-23 18:43:12', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD009', 'CUS001', NULL, '2025-12-23 18:54:27', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD010', 'CUS002', 'V0007', '2025-12-24 03:33:38', 'Bank Transfer', 'Standard', 0.00, 'Complete'),
('ORD011', 'CUS002', NULL, '2025-12-24 03:57:10', 'COD', 'Standard', 30000.00, 'Cancelled'),
('ORD012', 'CUS003', NULL, '2025-12-24 04:42:42', 'Bank Transfer', 'Express', 50000.00, 'Returned'),
('ORD013', 'CUS003', NULL, '2025-12-24 04:46:09', 'Bank Transfer', 'Standard', 30000.00, 'Cancelled'),
('ORD014', 'CUS002', NULL, '2025-12-24 05:02:00', 'COD', 'Standard', 30000.00, 'Complete'),
('ORD015', 'CUS002', NULL, '2025-12-24 06:27:25', 'Bank Transfer', 'Standard', 0.00, 'Cancelled'),
('ORD016', 'CUS002', NULL, '2025-12-24 06:31:18', 'COD', 'Standard', 0.00, 'Returned'),
('ORD017', 'CUS004', NULL, '2025-12-24 06:58:22', 'Bank Transfer', 'Standard', 0.00, 'Cancelled'),
('ORD018', 'CUS004', NULL, '2025-12-24 07:04:57', 'COD', 'Express', 0.00, 'Complete'),
('ORD019', 'CUS005', NULL, '2025-12-24 10:02:04', 'Bank Transfer', 'Standard', 0.00, 'Cancelled'),
('ORD020', 'CUS005', NULL, '2025-12-24 10:04:25', 'COD', 'Standard', 0.00, 'Returned');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_detail`
--

CREATE TABLE `order_detail` (
  `OrderID` varchar(10) NOT NULL,
  `SKUID` varchar(20) NOT NULL,
  `OrderQuantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_detail`
--

INSERT INTO `order_detail` (`OrderID`, `SKUID`, `OrderQuantity`) VALUES
('ORD001', 'MAR-002-125', 1),
('ORD002', 'CHG-001-100', 1),
('ORD003', 'MAR-002-125', 1),
('ORD004', 'MAR-002-125', 1),
('ORD005', 'CHG-001-100', 1),
('ORD006', 'FHC-002-100', 1),
('ORD007', 'MAR-004-125', 1),
('ORD008', 'FHC-001-100', 1),
('ORD009', 'HAC-002-100', 1),
('ORD010', 'MAR-002-125', 6),
('ORD011', 'FHC-002-100', 1),
('ORD012', 'CHG-003-200', 4),
('ORD013', 'CHG-003-200', 4),
('ORD014', 'FHC-002-100', 1),
('ORD015', 'LOL-001-15', 3),
('ORD016', 'LOL-001-15', 3),
('ORD017', 'CHG-003-100', 6),
('ORD018', 'CHG-003-100', 6),
('ORD019', 'LOL-001-15', 3),
('ORD020', 'LOL-001-15', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `ProductID` varchar(10) NOT NULL,
  `CategoryID` varchar(10) DEFAULT NULL,
  `ProductName` varchar(200) DEFAULT NULL,
  `Unit` varchar(50) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `Flavour` varchar(200) DEFAULT NULL,
  `Ingredient` varchar(500) DEFAULT NULL,
  `Filter` varchar(50) DEFAULT NULL,
  `Image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product`
--

INSERT INTO `product` (`ProductID`, `CategoryID`, `ProductName`, `Unit`, `Description`, `Flavour`, `Ingredient`, `Filter`, `Image`) VALUES
('CB-001', 'XMS', 'Royal Cocoa Bomb', 'Packet', 'A rich cocoa sphere that melts into warm milk, creating a luxurious chocolate drink with a silky finish.', 'Chocolate', 'Sugar-free', 'On sales', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CB-001_1766333045_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CB-001_1766334657_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CB-001_1766334666_0.png\",\"is_thumbnail\":false}]'),
('CHG-001', 'CHG', 'Blueberry Crisp Chewy', 'Packet', 'A crunchy-outside, chewy-inside candy that snaps lightly before releasing a sweet, elastic texture.', 'Fruit', 'Xylitol', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-001_1766333003_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-001_1766334469_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-001_1766334493_0.png\",\"is_thumbnail\":false}]'),
('CHG-002', 'CHG', 'Mint Crisp Chewy', 'Packet', 'Refreshing chewy candy with a crisp surface and a cool mint finish.', 'Fruit', 'Xylitol', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-002_1766332973_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-002_1766334513_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-002_1766334527_0.png\",\"is_thumbnail\":false}]'),
('CHG-003', 'CHG', 'Cola Crisp Chewy', 'Packet', 'Chewy cola-flavored bites with a slightly crisp coating for added texture.', 'Cola', 'Xylitol', 'Best-seller', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-003_1766332911_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-003_1766334546_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-003_1766334560_0.png\",\"is_thumbnail\":false}]'),
('CHG-004', 'CHG', 'Strawberry Soft Chewy', 'Packet', 'Gentle, night-inspired chews with a smooth texture and a soft glow of sweetness, perfect for slow snacking.', 'Fruit', 'Gluten-free, Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-004_1766332870_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-004_1766334585_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/CHG-004_1766334596_0.png\",\"is_thumbnail\":false}]'),
('FHC-001', 'FHC', 'Caramel-filled Coffee Candy', 'Packet', 'A rich coffee shell wrapped around a soft caramel center for a warm, indulgent sweetness.', 'Coffee, Caramel', 'Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-001_1766334726_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-001_1766334738_0.png\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-001_1766335075_0.webp\",\"is_thumbnail\":false}]'),
('FHC-002', 'FHC', 'Milk-filled Coffee Candy', 'Packet', 'Hard coffee candy with a creamy milk core, blending bold coffee notes with smooth dairy richness.', 'Coffee', 'Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-002_1766333086_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-002_1766334697_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/FHC-002_1766334707_0.png\",\"is_thumbnail\":false}]'),
('GUM-001', 'GUM', 'Wiggly Worm Gummies', 'Packet', 'Soft, stretchy gummies shaped like playful worms, offering a lively chew and bright fruit sweetness.', 'Fruit', 'Gelatin-free, Sugar-free', 'Best-seller', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-001_1766332806_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-001_1766334795_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-001_1766334807_0.png\",\"is_thumbnail\":false}]'),
('GUM-002', 'GUM', 'Tiny Bear Gummies', 'Packet', 'Miniature gummy bears with a bouncy texture and a clean, fruity finish that melts smoothly on the tongue.', 'Fruit', 'Gelatin-free, Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-002_1766332597_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-002_1766334760_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/GUM-002_1766334769_0.png\",\"is_thumbnail\":false}]'),
('HAC-001', 'HAC', 'Milk Coffee Candy', 'Packet', 'A smooth, creamy coffee candy with a gentle milk sweetness and a satisfying melt-in-mouth texture.', 'Coffee', 'Sugar-free', 'Best-seller', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-001_1766332406_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-001_1766334872_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-001_1766334882_0.png\",\"is_thumbnail\":false}]'),
('HAC-002', 'HAC', 'Fruit Candy', 'Packet', 'Bright and juicy hard candies bursting with assorted fruit sweetness in every piece.', 'Fruit', 'Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-002_1766332363_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-002_1766334838_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/HAC-002_1766334850_0.png\",\"is_thumbnail\":false}]'),
('KEO002', 'TEST001', 'Kẹo kẹo', 'Packet', '', '', '', '', NULL),
('KEONGON', 'TEST001', 'kẹo ngon', 'Packet', '', '', '', '', NULL),
('LOL-001', 'TET', 'Assorted Fruit Lolipop', 'Stick', 'Colorful fruit lollipops delivering long-lasting sweetness and classic fruity notes.', 'Fruit', 'Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/LOL-001_1766332305_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/LOL-001_1766335025_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/LOL-001_1766335033_0.png\",\"is_thumbnail\":false}]'),
('MAR-001', 'MAR', 'Vanila Cotton Whirl', 'Packet', 'Light, fluffy cotton candy infused with delicate vanilla sweetness.', 'Vanilla', 'Gluten-free, Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-001_1766332268_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-001_1766334947_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-001_1766334953_0.png\",\"is_thumbnail\":false}]'),
('MAR-002', 'MAR', 'Chocolate Cotton Whirl', 'Packet', 'Airy cotton candy with a cocoa-inspired taste that melts instantly on the tongue.', 'Chocolate', 'Gluten-free, Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-002_1766334204_0.png\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-002_1766334365_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-002_1766334376_0.png\",\"is_thumbnail\":false}]'),
('MAR-003', 'MAR', 'Strawberry Cotton Whirl', 'Packet', 'Fluffy strawberry-scented cotton candy offering a soft melt and fruity aroma.', 'Fruit', 'Gluten-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-003_1766332190_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-003_1766334927_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-003_1766334934_0.png\",\"is_thumbnail\":false}]'),
('MAR-004', 'MAR', 'Blueberry Fluffy Cloud', 'Packet', 'Ultra-light, soft confection with a cloud-like texture and a gentle blueberry sweetness.', 'Fruit', 'Gluten-free', 'New products', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-004_1766332121_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-004_1766334899_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/MAR-004_1766334913_0.png\",\"is_thumbnail\":false}]'),
('NOU-001', 'TET', 'Dried Fruit Nougat Candy', 'Packet', 'Soft, chewy nougat blended with pieces of dried fruit for a rich, wholesome sweetness.', 'Fruit', 'Sugar-free', NULL, '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/NOU-001_1766331992_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/NOU-001_1766334996_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/NOU-001_1766335002_0.png\",\"is_thumbnail\":false}]'),
('TOF-001', 'TET', 'Salted Caramel Toffee Candy', 'Packet', 'Smooth, buttery toffee with a hint of sea salt for a balanced, addicting sweetness.', 'Caramel', 'Sugar-free', 'New products', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/TOF-001_1766331917_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/TOF-001_1766334971_0.png\",\"is_thumbnail\":true},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/TOF-001_1766334980_0.png\",\"is_thumbnail\":false}]'),
('WS-001', 'XMS', 'Winter Stick', 'Stick', 'Cool, refreshing mint stick candy that delivers a crisp, clean winter-sweet sensation.', 'Fruit', 'Sugar-free', 'On sales', '[{\"path\":\"/Candy-Crunch-Website/views/website/img/products/WS-001_1766331607_0.webp\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/WS-001_1766334631_0.png\",\"is_thumbnail\":false},{\"path\":\"/Candy-Crunch-Website/views/website/img/products/WS-001_1766334638_0.png\",\"is_thumbnail\":true}]');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `refund`
--

CREATE TABLE `refund` (
  `RefundID` varchar(10) NOT NULL,
  `OrderID` varchar(10) DEFAULT NULL,
  `RefundDate` datetime DEFAULT NULL,
  `RefundReason` varchar(500) DEFAULT NULL,
  `RefundDescription` varchar(500) DEFAULT NULL,
  `RefundMethod` varchar(100) DEFAULT NULL,
  `RefundImage` varchar(500) DEFAULT NULL,
  `RefundStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `refund`
--

INSERT INTO `refund` (`RefundID`, `OrderID`, `RefundDate`, `RefundReason`, `RefundDescription`, `RefundMethod`, `RefundImage`, `RefundStatus`) VALUES
('RF001', 'ORD010', '2025-12-24 09:37:38', 'Product is expired', '', 'Refund via Bank transfer', '/views/website/img/refund/1766543858_f03c0c03-9c84-4523-b7fa-54aae7af8ec4.jpg', 'Approved'),
('RF002', 'ORD012', '2025-12-24 10:44:48', 'Product is crushed or deformed', '', 'Refund via Bank transfer', NULL, 'Approved'),
('RF003', 'ORD014', '2025-12-24 11:03:06', 'Product is expired', '', 'Refund via Bank transfer', NULL, 'Rejected'),
('RF004', 'ORD016', '2025-12-24 12:36:24', 'Product is expired', '', 'Refund via Bank transfer', NULL, 'Approved'),
('RF005', 'ORD020', '2025-12-24 16:06:34', 'Product is expired', '', 'Issue a Gift Card', NULL, 'Approved');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sku`
--

CREATE TABLE `sku` (
  `SKUID` varchar(20) NOT NULL,
  `ProductID` varchar(10) DEFAULT NULL,
  `InventoryID` varchar(10) DEFAULT NULL,
  `Attribute` int(11) DEFAULT NULL,
  `OriginalPrice` decimal(10,2) DEFAULT NULL,
  `PromotionPrice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sku`
--

INSERT INTO `sku` (`SKUID`, `ProductID`, `InventoryID`, `Attribute`, `OriginalPrice`, `PromotionPrice`) VALUES
('CB-001-125', 'CB-001', 'IVEN055', 125, 100000.00, 80000.00),
('CB-001-175', 'CB-001', 'IVEN056', 175, 140000.00, 112000.00),
('CB-001-250', 'CB-001', 'IVEN057', 250, 200000.00, 160000.00),
('CHG-001-100', 'CHG-001', 'IVEN019', 100, 50000.00, NULL),
('CHG-001-200', 'CHG-001', 'IVEN020', 200, 60000.00, NULL),
('CHG-001-300', 'CHG-001', 'IVEN021', 300, 70000.00, NULL),
('CHG-002-100', 'CHG-002', 'IVEN022', 100, 40000.00, NULL),
('CHG-002-200', 'CHG-002', 'IVEN023', 200, 50000.00, NULL),
('CHG-002-300', 'CHG-002', 'IVEN024', 300, 60000.00, NULL),
('CHG-003-100', 'CHG-003', 'IVEN025', 100, 40000.00, NULL),
('CHG-003-200', 'CHG-003', 'IVEN026', 200, 50000.00, NULL),
('CHG-003-300', 'CHG-003', 'IVEN027', 300, 60000.00, NULL),
('CHG-004-100', 'CHG-004', 'IVEN028', 100, 60000.00, NULL),
('CHG-004-200', 'CHG-004', 'IVEN029', 200, 70000.00, NULL),
('CHG-004-300', 'CHG-004', 'IVEN030', 300, 80000.00, NULL),
('FHC-001-100', 'FHC-001', 'IVEN007', 100, 60000.00, NULL),
('FHC-001-200', 'FHC-001', 'IVEN008', 200, 70000.00, NULL),
('FHC-001-300', 'FHC-001', 'IVEN009', 300, 80000.00, NULL),
('FHC-002-100', 'FHC-002', 'IVEN010', 100, 60000.00, NULL),
('FHC-002-200', 'FHC-002', 'IVEN011', 200, 70000.00, NULL),
('FHC-002-300', 'FHC-002', 'IVEN012', 300, 80000.00, NULL),
('GUM-001-100', 'GUM-001', 'IVEN013', 100, 50000.00, NULL),
('GUM-001-200', 'GUM-001', 'IVEN014', 200, 60000.00, NULL),
('GUM-001-300', 'GUM-001', 'IVEN015', 300, 70000.00, NULL),
('GUM-002-100', 'GUM-002', 'IVEN016', 100, 80000.00, NULL),
('GUM-002-200', 'GUM-002', 'IVEN017', 200, 100000.00, NULL),
('GUM-002-300', 'GUM-002', 'IVEN018', 300, 140000.00, NULL),
('HAC-001-100', 'HAC-001', 'IVEN001', 100, 50000.00, NULL),
('HAC-001-200', 'HAC-001', 'IVEN002', 200, 60000.00, NULL),
('HAC-001-300', 'HAC-001', 'IVEN003', 300, 70000.00, NULL),
('HAC-002-100', 'HAC-002', 'IVEN004', 100, 60000.00, NULL),
('HAC-002-200', 'HAC-002', 'IVEN005', 200, 70000.00, NULL),
('HAC-002-300', 'HAC-002', 'IVEN006', 300, 80000.00, NULL),
('LOL-001-15', 'LOL-001', 'IVEN043', 15, 90000.00, NULL),
('LOL-001-25', 'LOL-001', 'IVEN044', 25, 150000.00, NULL),
('LOL-001-40', 'LOL-001', 'IVEN045', 40, 240000.00, NULL),
('MAR-001-125', 'MAR-001', 'IVEN031', 125, 180000.00, NULL),
('MAR-001-250', 'MAR-001', 'IVEN032', 250, 260000.00, NULL),
('MAR-001-500', 'MAR-001', 'IVEN033', 500, 380000.00, NULL),
('MAR-002-125', 'MAR-002', 'IVEN034', 125, 180000.00, NULL),
('MAR-002-250', 'MAR-002', 'IVEN035', 250, 260000.00, NULL),
('MAR-002-500', 'MAR-002', 'IVEN036', 500, 380000.00, NULL),
('MAR-003-125', 'MAR-003', 'IVEN037', 125, 220000.00, NULL),
('MAR-003-250', 'MAR-003', 'IVEN038', 250, 300000.00, NULL),
('MAR-003-500', 'MAR-003', 'IVEN039', 500, 420000.00, NULL),
('MAR-004-125', 'MAR-004', 'IVEN040', 125, 220000.00, NULL),
('MAR-004-250', 'MAR-004', 'IVEN041', 250, 300000.00, NULL),
('MAR-004-500', 'MAR-004', 'IVEN042', 500, 420000.00, NULL),
('NOU-001-125', 'NOU-001', 'IVEN046', 125, 280000.00, NULL),
('NOU-001-250', 'NOU-001', 'IVEN047', 250, 340000.00, NULL),
('NOU-001-500', 'NOU-001', 'IVEN048', 500, 450000.00, NULL),
('SKU001', 'KEONGON', 'IVEN059', 100, 1000000.00, 500000.00),
('SKU003', 'KEO002', 'IVEN060', 100, 500000.00, NULL),
('TOF-001-125', 'TOF-001', 'IVEN049', 125, 320000.00, NULL),
('TOF-001-250', 'TOF-001', 'IVEN050', 250, 380000.00, NULL),
('TOF-001-500', 'TOF-001', 'IVEN051', 500, 450000.00, NULL),
('WS-001-10', 'WS-001', 'IVEN052', 10, 120000.00, 96000.00),
('WS-001-15', 'WS-001', 'IVEN053', 15, 180000.00, 126000.00),
('WS-001-20', 'WS-001', 'IVEN054', 20, 240000.00, 192000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher`
--

CREATE TABLE `voucher` (
  `VoucherID` varchar(10) NOT NULL,
  `Code` varchar(50) DEFAULT NULL,
  `VoucherDescription` varchar(500) DEFAULT NULL,
  `DiscountPercent` decimal(5,2) DEFAULT NULL,
  `DiscountAmount` decimal(10,2) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `MinOrder` decimal(10,2) DEFAULT NULL,
  `VoucherStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `voucher`
--

INSERT INTO `voucher` (`VoucherID`, `Code`, `VoucherDescription`, `DiscountPercent`, `DiscountAmount`, `StartDate`, `EndDate`, `MinOrder`, `VoucherStatus`) VALUES
('V0001', 'SWEETSUMMER10', 'Enjoy 10% off on all summer candy collections.', 10.00, 0.00, '2025-06-01', '2025-06-30', 300000.00, 'Expired'),
('V0002', 'FREESHIP-SNACKS', 'Get 50,000 VND off shipping fee for all snack orders.', 0.00, 50000.00, '2025-06-01', '2025-08-01', 200000.00, 'Expired'),
('V0003', 'NEWBITE15', 'New customers receive 15% off any sweet treats.', 15.00, 0.00, '2025-01-01', '2025-12-31', 0.00, 'Expiring Soon'),
('V0004', 'CANDYDEAL30', '30% discount on premium imported candies.', 30.00, 0.00, '2025-05-01', '2025-05-31', 500000.00, 'Expired'),
('V0005', 'SUGARFLASH100K', 'Instant 100,000 VND off for large candy and snack orders.', 0.00, 100000.00, '2025-06-15', '2025-06-20', 800000.00, 'Expired'),
('V0006', 'WEEKEND-SWEET20', 'Weekend offer: 20% off on selected sweets.', 20.00, 0.00, '2025-06-07', '2025-06-09', 400000.00, 'Expired'),
('V0007', 'VIP-CANDY25', 'Exclusive 25% discount for VIP snack lovers.', 25.00, 0.00, '2025-01-01', '2025-12-31', 1000000.00, 'Expiring Soon'),
('V0008', 'MEGASWEET70', 'Massive 70% off on clearance candy items.', 70.00, 0.00, '2025-06-01', '2025-06-10', 300000.00, 'Expired'),
('V0009', 'BUYMORE-SNACK40K', 'Save 40,000 VND when stocking up on snacks.', 0.00, 40000.00, '2025-06-01', '2025-07-15', 150000.00, 'Expired'),
('V0010', 'MIDYEAR-SWEET20', 'Mid-year sale: 20% off all candies and treats.', 20.00, 0.00, '2025-08-01', '2026-01-30', 250000.00, 'Active'),
('V0011', 'SUMMER', '', 10.00, 0.00, '2025-12-24', '2026-01-23', 500000.00, 'Active'),
('V0012', 'OMG', '', 0.00, 100000.00, '2025-12-24', '2026-01-23', 500000.00, 'Active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `CustomerID` varchar(10) NOT NULL,
  `ProductID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlist`
--

INSERT INTO `wishlist` (`CustomerID`, `ProductID`) VALUES
('CUS001', 'MAR-002'),
('CUS002', 'NOU-001'),
('CUS002', 'TOF-001'),
('CUS003', 'CHG-003'),
('CUS004', 'CHG-003');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`AccountID`);

--
-- Chỉ mục cho bảng `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Chỉ mục cho bảng `banking`
--
ALTER TABLE `banking`
  ADD PRIMARY KEY (`BankingID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Chỉ mục cho bảng `cancellation`
--
ALTER TABLE `cancellation`
  ADD PRIMARY KEY (`CancellationID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CartID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Chỉ mục cho bảng `cart_detail`
--
ALTER TABLE `cart_detail`
  ADD PRIMARY KEY (`CartID`,`SKUID`),
  ADD KEY `fk_cartdetail_sku` (`SKUID`);

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Chỉ mục cho bảng `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD KEY `AccountID` (`AccountID`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `fk_feedback_sku` (`SKUID`);

--
-- Chỉ mục cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`InventoryID`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `VoucherID` (`VoucherID`);

--
-- Chỉ mục cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`OrderID`,`SKUID`),
  ADD KEY `fk_orderdetail_sku` (`SKUID`);

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Chỉ mục cho bảng `refund`
--
ALTER TABLE `refund`
  ADD PRIMARY KEY (`RefundID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Chỉ mục cho bảng `sku`
--
ALTER TABLE `sku`
  ADD PRIMARY KEY (`SKUID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `InventoryID` (`InventoryID`);

--
-- Chỉ mục cho bảng `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`VoucherID`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`CustomerID`,`ProductID`);

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Các ràng buộc cho bảng `banking`
--
ALTER TABLE `banking`
  ADD CONSTRAINT `banking_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Các ràng buộc cho bảng `cancellation`
--
ALTER TABLE `cancellation`
  ADD CONSTRAINT `cancellation_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Các ràng buộc cho bảng `cart_detail`
--
ALTER TABLE `cart_detail`
  ADD CONSTRAINT `fk_cartdetail_cart` FOREIGN KEY (`CartID`) REFERENCES `cart` (`CartID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cartdetail_sku` FOREIGN KEY (`SKUID`) REFERENCES `sku` (`SKUID`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `account` (`AccountID`);

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`),
  ADD CONSTRAINT `fk_feedback_sku` FOREIGN KEY (`SKUID`) REFERENCES `sku` (`SKUID`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`VoucherID`) REFERENCES `voucher` (`VoucherID`);

--
-- Các ràng buộc cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `fk_orderdetail_order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderdetail_sku` FOREIGN KEY (`SKUID`) REFERENCES `sku` (`SKUID`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `category` (`CategoryID`);

--
-- Các ràng buộc cho bảng `refund`
--
ALTER TABLE `refund`
  ADD CONSTRAINT `refund_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);

--
-- Các ràng buộc cho bảng `sku`
--
ALTER TABLE `sku`
  ADD CONSTRAINT `sku_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`),
  ADD CONSTRAINT `sku_ibfk_2` FOREIGN KEY (`InventoryID`) REFERENCES `inventory` (`InventoryID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
