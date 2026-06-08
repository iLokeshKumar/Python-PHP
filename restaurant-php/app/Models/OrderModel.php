<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_date'];

    private BaseConnection $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getOrdersWithDetails(): array
    {
        $orders = $this->db
            ->query('SELECT id AS order_id, order_date FROM orders ORDER BY order_date, id')
            ->getResultArray();

        foreach ($orders as &$order) {
            $order = $this->attachDetails($order);
        }

        return $orders;
    }

    public function getOrderWithDetails(int $orderId): ?array
    {
        $order = $this->db
            ->query('SELECT id AS order_id, order_date FROM orders WHERE id = ?', [$orderId])
            ->getRowArray();

        if (!$order) {
            return null;
        }

        return $this->attachDetails($order);
    }

    private function attachDetails(array $order): array
    {
        $order['items']    = $this->getItems($order['order_id']);
        $order['payments'] = $this->getPayments($order['order_id']);

        $orderTotal  = array_sum(array_column($order['items'], 'total'));
        $totalPaid   = array_sum(array_column($order['payments'], 'total_paid'));
        $amountDue   = !empty($order['payments']) ? (float) $order['payments'][0]['amount_due'] : $orderTotal;

        $order['order_total']        = round($orderTotal, 4);
        $order['amount_due']         = round($amountDue, 4);
        $order['total_paid']         = round($totalPaid, 4);
        $order['balance_remaining']  = round($amountDue - $totalPaid, 4);
        $order['payment_count']      = count($order['payments']);
        $order['item_count']         = array_sum(array_column($order['items'], 'qty'));

        return $order;
    }

    private function getItems(int $orderId): array
    {
        return $this->db->query(
            'SELECT
                oi.id,
                oi.item_id,
                mi.name        AS item_name,
                c.name         AS category,
                m.name         AS menu,
                oi.size,
                oi.price,
                oi.qty,
                oi.total,
                oi.order_status
             FROM order_items oi
             JOIN menu_items mi ON mi.id = oi.item_id
             JOIN categories c  ON c.id  = mi.category_id
             JOIN menus m       ON m.id  = mi.menu_id
             WHERE oi.order_id = ?
             ORDER BY oi.id',
            [$orderId]
        )->getResultArray();
    }

    private function getPayments(int $orderId): array
    {
        return $this->db->query(
            'SELECT
                payment_id,
                payment_date,
                amount_due,
                tips,
                discount,
                total_paid,
                payment_type,
                payment_status
             FROM payments
             WHERE order_id = ?
             ORDER BY payment_id',
            [$orderId]
        )->getResultArray();
    }
}