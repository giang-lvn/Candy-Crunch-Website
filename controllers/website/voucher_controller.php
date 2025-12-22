<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/voucher_model.php';

class VoucherController {

    private $voucherModel;

    public function __construct() {
        $this->voucherModel = new VoucherModel();
    }

    /* ==================================================
       FLOW 1 – GIỐNG ORDERS (LOAD TRANG MY VOUCHERS)
       ================================================== */
    public function getMyVouchers() {
        session_start();

        if (!isset($_SESSION['user_data']['CustomerID'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            exit;
        }

        $vouchers = $this->voucherModel->getActiveVouchers();

        echo json_encode([
            'success'  => true,
            'vouchers' => $this->mapVouchers($vouchers)
        ]);
        exit;
    }

    /* ==================================================
       FLOW 2 – API LIST (FILTER)
       ================================================== */
    public function list() {
        $filter = $_GET['filter'] ?? 'all';
        $vouchers = $this->voucherModel->getVoucherByFilter($filter);
        $this->jsonResponse(true, $this->mapVouchers($vouchers));
    }

    /* ==================================================
       FLOW 3 – APPLY VOUCHER
       ================================================== */
    public function apply() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, null, 'Invalid request method');
        }

        $voucherId  = $_POST['voucher_id'] ?? null;
        $orderTotal = $_POST['order_total'] ?? null;

        if ($voucherId === null || $orderTotal === null) {
            $this->jsonResponse(false, null, 'Missing parameters');
        }

        $orderTotal = (float)$orderTotal;

        if ($orderTotal <= 0) {
            $this->jsonResponse(false, null, 'Invalid order total');
        }

        $voucher = $this->voucherModel->getVoucherById($voucherId);

        if (!$voucher) {
            $this->jsonResponse(false, null, 'Voucher not found or expired');
        }

        if ($orderTotal < $voucher['MinOrder']) {
            $this->jsonResponse(
                false,
                null,
                'Order total does not meet minimum requirement'
            );
        }

        $discountValue = 0;

        if ($voucher['DiscountPercent'] > 0) {
            $discountValue = $orderTotal * ($voucher['DiscountPercent'] / 100);
        } elseif ($voucher['DiscountAmount'] > 0) {
            $discountValue = $voucher['DiscountAmount'];
        }

        $discountValue = min($discountValue, $orderTotal);

        $this->jsonResponse(true, [
            'voucher_id'   => $voucher['VoucherID'],
            'code'         => $voucher['Code'],
            'discount'     => $discountValue,
            'discountText' => $voucher['DiscountText']
        ]);
    }

    /* ==================================================
       MAP DATA (GIỐNG Orders)
       ================================================== */
    private function mapVouchers($vouchers) {
        return array_map(function ($v) {
            // Determine badge text
            $badge = null;
            if (($v['DynamicStatus'] ?? '') === 'Upcoming') {
                $badge = 'Upcoming';
            } elseif (($v['DynamicStatus'] ?? '') === 'Expiring Soon') {
                $badge = 'Expiring Soon';
            }
            
            return [
                'id'           => $v['VoucherID'],
                'code'         => $v['Code'],
                'description'  => $v['VoucherDescription'],
                'discountText' => $v['DiscountText'],
                'minOrder'     => number_format($v['MinOrder'], 0, ',', '.') . 'đ',
                'startDate'    => date('d/m/Y', strtotime($v['StartDate'])),
                'expireDate'   => date('d/m/Y', strtotime($v['EndDate'])),
                'daysLeft'     => (int)$v['DaysUntilExpire'],
                'badge'        => $badge,
                'isUpcoming'   => ($v['DynamicStatus'] ?? '') === 'Upcoming'
            ];
        }, $vouchers);
    }

    private function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }
}

/* ==================================================
   ROUTER NHẸ – KHÔNG PHÁ FLOW ORDERS
   ================================================== */

$controller = new VoucherController();

/* gọi trực tiếp (giống Orders) */
if (!isset($_GET['action'])) {
    $controller->getMyVouchers();
}

/* gọi bằng fetch */
switch ($_GET['action']) {
    case 'list':
        $controller->list();
        break;

    case 'apply':
        $controller->apply();
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}
