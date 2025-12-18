<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/changepass_model.php';

if (!isset($_SESSION['AccountID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$current = $_POST['currentPassword'] ?? '';
$new     = $_POST['newPassword'] ?? '';

if ($current === '' || $new === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$model = new ChangePasswordModel();
$data  = $model->getPasswordByAccountId($_SESSION['AccountID']);

if (!$data || !password_verify($current, $data['Password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Current password incorrect']);
    exit;
}

$hashed = password_hash($new, PASSWORD_BCRYPT);

if ($model->updatePassword($_SESSION['AccountID'], $hashed)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
