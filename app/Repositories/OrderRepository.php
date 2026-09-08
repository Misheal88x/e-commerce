<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Core\Database\BaseRepository;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    public function findByIdAndUserId(int $id, int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM orders WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    public function findByUserId(int $userId, int $page, int $perPage, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [$userId];

        $statusClause = '';
        if ($status !== null) {
            $statusClause = ' AND o.status = ?';
            $params[]     = $status;
        }

        $params[] = $perPage;
        $params[] = $offset;

        return $this->fetchAll(
            'SELECT o.*, COUNT(oi.id) AS total_items
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ?' . $statusClause . '
             GROUP BY o.id
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?',
            $params
        );
    }

    public function countByUserId(int $userId, ?string $status = null): int
    {
        $params = [$userId];

        $statusClause = '';
        if ($status !== null) {
            $statusClause = ' AND status = ?';
            $params[]     = $status;
        }

        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM orders WHERE user_id = ?' . $statusClause,
            $params
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO orders (
                user_id, address_id, delivery_type_id, payment_type_id,
                coupon_id, coupon_code, subtotal, delivery_fee, discount_amount,
                total_price, status, notes,
                address_city, address_street, address_building, address_info,
                address_latitude, address_longitude,
                delivery_name, delivery_time, payment_name,
                created_at, updated_at
             ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
             )',
            [
                $data['user_id'],         $data['address_id'],
                $data['delivery_type_id'],$data['payment_type_id'],
                $data['coupon_id']        ?? null,
                $data['coupon_code']      ?? null,
                $data['subtotal'],        $data['delivery_fee'],
                $data['discount_amount'], $data['total_price'],
                $data['status'],          $data['notes'] ?? null,
                $data['address_city'],    $data['address_street'],
                $data['address_building'],$data['address_info'],
                $data['address_latitude'],$data['address_longitude'],
                $data['delivery_name'],   $data['delivery_time'],
                $data['payment_name'],
            ]
        );
    }

    public function createItem(array $data): void
    {
        $this->execute(
            'INSERT INTO order_items (
                order_id, product_id, product_name_snapshot,
                product_price_snapshot, offer_price_snapshot,
                quantity, selected_options_json
             ) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['order_id'],
                $data['product_id'],
                $data['product_name_snapshot'],
                $data['product_price_snapshot'],
                $data['offer_price_snapshot'] ?? null,
                $data['quantity'],
                $data['selected_options_json'] ?? null,
            ]
        );
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        return $this->execute(
            'UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, $orderId]
        ) > 0;
    }

    public function findItems(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT oi.*, p.main_image AS product_image, p.slug AS product_slug
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?',
            [$orderId]
        );
    }

    public function findStatusHistory(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT status, note, created_at FROM order_status_history
             WHERE order_id = ? ORDER BY created_at ASC',
            [$orderId]
        );
    }
}
