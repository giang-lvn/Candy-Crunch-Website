<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/changepass_model.php';

/* ✅ 1. CHECK LOGIN */
if (!isset($_SESSION['user_data']['CustomerID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

/* ✅ 2. LẤY ĐÚNG CUSTOMERID */
$customerId = $_SESSION['user_data']['CustomerID'];

/* ✅ 3. LẤY INPUT */
$current = $_POST['currentPassword'] ?? '';
$new     = $_POST['newPassword'] ?? '';

if ($current === '' || $new === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

/* ✅ 4. CHECK PASSWORD */
$model = new ChangePasswordModel();
$data  = $model->getPasswordByCustomerId($customerId);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Account not found']);
    exit;
}

if (!password_verify($current, $data['Password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Current password incorrect']);
    exit;
}

/* 🚫 CHẶN TRÙNG */
if (password_verify($new, $data['Password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'New password must be different from current password'
    ]);
    exit;
}

/* ✅ 5. UPDATE */
$hashed = password_hash($new, PASSWORD_BCRYPT);

if ($model->updatePasswordByCustomerId($customerId, $hashed)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
