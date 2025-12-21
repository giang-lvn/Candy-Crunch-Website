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
        return array_map(function ($o) {
            return [
                'id' => $o['OrderID'],
                'status' => $this->mapStatus($o['OrderStatus']),
                'statusText' => $this->mapStatusText($o['OrderStatus']),
                'date' => date('d F Y', strtotime($o['OrderDate'])),
                'product' => $o['ProductName'],
                'weight' => $o['Attribute'] . 'g',
                'quantity' => (int)$o['Quantity'],
                'total' => number_format($o['TotalPrice'], 0, ',', '.') . ' VND',
                'buttons' => $this->mapButtons($o['OrderStatus'])
            ];
        }, $orders);
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
            'Pending Confirmation'   => ['Confirmed', 'Cancel'],
            default                  => ['Contact']
        };
    }
}

/* 🔥 BẮT BUỘC PHẢI CÓ */
$controller = new OrderController();
$controller->getMyOrder();
