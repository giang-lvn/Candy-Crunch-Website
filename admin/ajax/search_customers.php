<?php
// admin/ajax/search_customers.php
// API tìm kiếm khách hàng theo email hoặc tên

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isAdminLoggedIn()) {
    echo json_encode(['results' => [], 'error' => 'Unauthorized']);
    exit;
}

$search = $_GET['q'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    // Đếm tổng số
    $countSql = "
        SELECT COUNT(*) as total
        FROM CUSTOMER c 
        LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID 
        WHERE a.Email LIKE ? 
           OR c.FirstName LIKE ? 
           OR c.LastName LIKE ?
           OR CONCAT(c.FirstName, ' ', c.LastName) LIKE ?
    ";
    $searchTerm = "%$search%";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $total = $countStmt->fetch()['total'];
    
    // Lấy danh sách khách hàng
    $sql = "
        SELECT 
            c.CustomerID,
            c.FirstName,
            c.LastName,
            a.AccountID,
            a.Email,
            a.AccountStatus,
            (SELECT addr.Phone FROM ADDRESS addr WHERE addr.CustomerID = c.CustomerID ORDER BY addr.AddressDefault DESC LIMIT 1) as Phone
        FROM CUSTOMER c 
        LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID 
        WHERE a.Email LIKE ? 
           OR c.FirstName LIKE ? 
           OR c.LastName LIKE ?
           OR CONCAT(c.FirstName, ' ', c.LastName) LIKE ?
        ORDER BY a.Email ASC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    $customers = $stmt->fetchAll();
    
    // Format kết quả cho Select2
    $results = [];
    foreach ($customers as $customer) {
        $name = trim($customer['FirstName'] . ' ' . $customer['LastName']);
        $email = $customer['Email'] ?? 'No email';
        $phone = $customer['Phone'] ?? '';
        $status = $customer['AccountStatus'] ?? 'Unknown';
        
        // Text hiển thị: Email - Tên (Phone) [Status]
        $text = $email . ' - ' . $name;
        if ($phone) {
            $text .= ' (' . $phone . ')';
        }
        if ($status !== 'Active') {
            $text .= ' [' . $status . ']';
        }
        
        $results[] = [
            'id' => $customer['CustomerID'],
            'text' => $text,
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'status' => $status
        ];
    }
    
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => ($page * $limit) < $total
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'results' => [],
        'error' => $e->getMessage()
    ]);
}
