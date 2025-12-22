<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/orders_model.php';

class OrderController {

    public function getMyOrder() {
        session_start();

        if (!isset($_SESSION['user_data']['CustomerID'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $customerId = $_SESSION['user_data']['CustomerID'];
        $model = new OrderModel();

        $orders = $model->getOrdersByCustomer($customerId);

        // 🔍 DEBUG – bật khi cần
        /*
        echo '<pre>';
        print_r($orders);
        exit;
        */

        echo json_encode([
            'success' => true,
            'orders' => $this->mapOrders($orders)
        ]);
        exit;
    }

    private function mapOrders($orders) {
        $grouped = [];

        foreach ($orders as $o) {
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
                        'code' => $o['VoucherCode'],
                        'amount' => $o['DiscountAmount'],
                        'percent' => $o['DiscountPercent'],
                        'min' => $o['MinOrder']
                    ],
                    'buttons' => $this->mapButtons($o['OrderStatus'])
                ];
            }

            // Add product
            $grouped[$id]['products'][] = [
                'name' => $o['ProductName'],
                'image' => $this->parseProductImage($o['Image']),
                'weight' => $o['Attribute'] . 'g',
                'quantity' => (int)$o['Quantity'],
                'price' => number_format($o['SubTotal'], 0, ',', '.') . ' VND'
            ];
            
            // Add SKUID for rating
            $grouped[$id]['productSkuIds'][] = $o['SKUID'] ?? '';

            // Accumulate subtotal
            $grouped[$id]['totalRaw'] += $o['SubTotal'];
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
            
            // Ensure total is not negative
            $finalTotal = max(0, $subTotal - $discount);
            $order['total'] = number_format($finalTotal, 0, ',', '.') . ' VND';
            
            // Clean up temporary keys
            unset($order['totalRaw']);
            unset($order['voucher']);
        }

        return array_values($grouped);
    }

    private function mapStatus($status) {
        return match ($status) {
            'Waiting Payment'        => 'waiting-payment',
            'Complete', 'Completed'  => 'completed',
            'Pending'                => 'pending',
            'On Shipping'            => 'on-shipping',
            'Returned'               => 'return',
            'Cancelled', 'Canceled'  => 'cancel',
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
            'Returned'               => 'Return',
            'Cancelled', 'Canceled'  => 'Cancel',
            'Pending Confirmation'   => 'Pending Confirmation',
            default                  => 'Pending'
        };
    }

    private function mapButtons($status) {
        return match ($status) {
            'Waiting Payment'        => ['Pay Now', 'Change Method'],
            'Complete', 'Completed'  => ['Buy Again', 'Return', 'Write Review'],
            'Pending'                => ['Contact'],
            'On Shipping'            => ['Cancel', 'Contact'],
            'Returned'               => ['Contact'],
            'Cancelled', 'Canceled'  => ['Contact', 'Buy Again'],
            'Pending Confirmation'   => ['Cancel'],
            default                  => ['Contact']
        };
    }

    private function parseProductImage($imageField) {
        if (empty($imageField)) return null;

        // Try to decode JSON
        $images = json_decode($imageField, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($images)) {
            // Find thumbnail
            foreach ($images as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'];
                }
            }
            // Fallback to first image
            return $images[0]['path'] ?? null;
        }

        // Handle legacy plain filenames (assume they are in ../img/)
        // Check if it's already a path
        if (strpos($imageField, '/') !== false) {
            return $imageField;
        }

        // If it is a simple filename, prepend standard image path
        // (Adjust this path based on where legacy images are actually stored)
        return '../img/' . $imageField;
    }
}

/* 🔥 BẮT BUỘC PHẢI CÓ */
$controller = new OrderController();
$controller->getMyOrder();
