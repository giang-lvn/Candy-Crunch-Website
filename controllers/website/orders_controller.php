<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/orders_model.php';

class OrderController {

    public function getMyOrder() {
        session_start();

        // Check multiple session variable names for compatibility
        $customerId = null;
        
        if (isset($_SESSION['user_data']['CustomerID'])) {
            $customerId = $_SESSION['user_data']['CustomerID'];
        } elseif (isset($_SESSION['customer_id'])) {
            $customerId = $_SESSION['customer_id'];
        } elseif (isset($_SESSION['CustomerID'])) {
            $customerId = $_SESSION['CustomerID'];
        }

        if (!$customerId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            $model = new OrderModel();

            $rawOrders = $model->getOrdersByCustomer($customerId);

            // Gộp các sản phẩm có cùng OrderID thành 1 đơn hàng
            $groupedOrders = $this->groupOrdersByOrderId($rawOrders);

            echo json_encode([
                'success' => true,
                'orders' => $groupedOrders
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Gộp các dòng có cùng OrderID thành 1 đơn hàng với danh sách products
     */
    private function groupOrdersByOrderId($rawOrders) {
        $grouped = [];

        foreach ($rawOrders as $o) {
            $id = $o['OrderID'];

            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'id' => $id,
                    'status' => $this->mapStatus($o['OrderStatus']),
                    'statusText' => $this->mapStatusText($o['OrderStatus']),
                    'date' => date('d F Y', strtotime($o['OrderDate'])),
                    'products' => [],
                    'productSkuIds' => [],
                    'totalRaw' => 0,
                    'voucher' => [
                        'code' => $o['VoucherCode'] ?? null,
                        'amount' => $o['DiscountAmount'] ?? 0,
                        'percent' => $o['DiscountPercent'] ?? 0,
                        'min' => $o['MinOrder'] ?? 0
                    ],
                    'buttons' => $this->mapButtons($o['OrderStatus']),
                    'canCancel' => $this->canCancel($o['OrderStatus']),
                    'canReturn' => $this->canReturn($o['OrderStatus'])
                ];
            }

            // Chỉ thêm sản phẩm nếu có ProductName
            if (!empty($o['ProductName'])) {
                // Add product with sku_id for rating
                $grouped[$id]['products'][] = [
                    'sku_id' => $o['SKUID'] ?? '',
                    'name' => $o['ProductName'],
                    'image' => $this->parseProductImage($o['Image']),
                    'weight' => ($o['Attribute'] ?? '') . 'g',
                    'quantity' => (int)($o['Quantity'] ?? 0),
                    'itemTotal' => number_format($o['SubTotal'] ?? 0, 0, ',', '.') . ' VND'
                ];
                
                // Add SKUID for rating (backward compatibility)
                $grouped[$id]['productSkuIds'][] = $o['SKUID'] ?? '';

                // Accumulate subtotal
                $grouped[$id]['totalRaw'] += floatval($o['SubTotal'] ?? 0);
            }
        }

        // Finalize totals
        foreach ($grouped as &$order) {
            $subTotal = $order['totalRaw'];
            $discount = 0;
            $v = $order['voucher'];

            if (!empty($v['code']) && $subTotal >= $v['min']) {
                if (!empty($v['percent'])) {
                    $discount = $subTotal * ($v['percent'] / 100);
                } elseif (!empty($v['amount'])) {
                    $discount = $v['amount'];
                }
            }

            $total = $subTotal - $discount;
            $order['total'] = number_format($total, 0, ',', '.') . ' VND';
            
            // Xóa các field không cần thiết cho frontend
            unset($order['voucher']);
            unset($order['totalRaw']);
        }

        // Chuyển từ associative array sang indexed array
        return array_values($grouped);
    }

    /**
     * Parse ảnh sản phẩm từ JSON và trả về URL thumbnail
     */
    private function parseProductImage($imageData) {
        if (empty($imageData)) {
            return null;
        }
        
        // Thử parse JSON
        $decoded = json_decode($imageData, true);
        
        if (is_array($decoded)) {
            // Tìm ảnh thumbnail
            foreach ($decoded as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'] ?? null;
                }
            }
            // Nếu không có thumbnail, lấy ảnh đầu tiên
            if (!empty($decoded[0])) {
                return is_array($decoded[0]) ? ($decoded[0]['path'] ?? null) : $decoded[0];
            }
            return null;
        }
        
        // Nếu không phải JSON, trả về nguyên bản
        return $imageData;
    }

    private function mapStatus($status) {
        return match ($status) {
            'Waiting Payment'        => 'waiting-payment',
            'Complete', 'Completed'  => 'completed',
            'Pending'                => 'pending',
            'On Shipping'            => 'on-shipping',
            'Pending Cancel'         => 'pending-cancel',
            'Pending Return'         => 'pending-return',
            'Returned'               => 'return',
            'Cancelled'              => 'cancel',
            'Pending Confirmation'   => 'pending-confirm',
            default                  => 'pending'
        };
    }

    private function mapStatusText($status) {
        return match ($status) {
            'Waiting Payment'        => 'Waiting Payment',
            'Complete', 'Completed'  => 'Completed',
            'Pending'                => 'Pending',
            'On Shipping'            => 'On Shipping',
            'Pending Cancel'         => 'Pending Cancel',
            'Pending Return'         => 'Pending Return',
            'Returned'               => 'Returned',
            'Cancelled'              => 'Cancelled',
            'Pending Confirmation'   => 'Pending Confirmation',
            default                  => 'Pending'
        };
    }

    private function mapButtons($status) {
        return match ($status) {
            'Waiting Payment'        => ['Pay Now', 'Cancel'],
            'Pending Confirmation'   => ['Cancel'],
            'Pending'                => ['Cancel', 'Contact'],
            'On Shipping'            => ['Contact'],
            'Complete', 'Completed'  => ['Buy Again', 'Return', 'Write Review'],
            'Pending Cancel'         => ['Contact'],
            'Pending Return'         => ['Contact'],
            'Returned'               => ['Contact', 'Buy Again'],
            'Cancelled'              => ['Contact', 'Buy Again'],
            default                  => ['Contact']
        };
    }

    // Kiểm tra có thể hủy đơn không
    private function canCancel($status) {
        return in_array($status, ['Waiting Payment', 'Pending Confirmation', 'Pending']);
    }

    // Kiểm tra có thể trả hàng không
    private function canReturn($status) {
        return in_array($status, ['Complete', 'Completed']);
    }
}

/* 🔥 BẮT BUỘC PHẢI CÓ */
$controller = new OrderController();
$controller->getMyOrder();
