<?php
/**
 * Pawfect Pet Shop - Order Model
 * Handles order data and operations
 */

require_once 'models/Product.php';

class Order extends Model {
    protected $table = 'orders';
    protected $fillable = [
        'order_number', 'user_id', 'status', 'subtotal', 'tax', 'shipping_cost', 'total',
        'shipping_address', 'shipping_city', 'shipping_barangay', 'shipping_zip',
        'payment_method', 'notes', 'shipped_date', 'delivery_date', 'cancelled_date'
    ];

    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function createOrder($userId, $totalAmount, $deliveryAddressId, $paymentMethod) {
        $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address_id, payment_method, order_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $totalAmount, $deliveryAddressId, $paymentMethod]);
        return $this->pdo->lastInsertId();
    }
    
    public function addItem($orderId, $productId, $quantity, $price) {
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$orderId, $productId, $quantity, $price]);
    }
    
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT o.*, o.shipped_date, da.city, da.barangay, da.street, da.zipcode FROM orders o LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id WHERE o.user_id = ? ORDER BY o.id DESC");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['items'] = $this->getItems($order['id']);
            $order['delivery_address'] = [
                'city' => $order['city'],
                'barangay' => $order['barangay'],
                'street' => $order['street'],
                'zipcode' => $order['zipcode']
            ];
        }
        return $orders;
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT o.*, u.first_name, u.last_name, u.email, da.city, da.barangay, da.street, da.zipcode FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id ORDER BY o.id DESC");
        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['items'] = $this->getItems($order['id']);
            $order['delivery_address'] = [
                'city' => $order['city'],
                'barangay' => $order['barangay'],
                'street' => $order['street'],
                'zipcode' => $order['zipcode']
            ];
        }
        return $orders;
    }
    
    public function getItems($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.product_image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    public function updateStatusByPdo($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function getStats() {
        $stmt = $this->pdo->query("SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
            FROM orders");
        return $stmt->fetch();
    }
    
    public function createFromCart($userId, $shippingData) {
        $cartModel = new Cart();
        $cartSummary = $cartModel->getCartSummary($userId);
        
        if (empty($cartSummary['items'])) {
            return false;
        }
        
        // Validate cart before creating order
        $cartIssues = $cartModel->validateCart($userId);
        if (!empty($cartIssues)) {
            return false; // Cart has issues
        }
        
        $orderData = [
            'order_number' => generate_order_number(),
            'user_id' => $userId,
            'status' => 'pending',
            'subtotal' => $cartSummary['subtotal'],
            'tax' => $cartSummary['tax'],
            'shipping_cost' => $cartSummary['shipping_cost'],
            'total' => $cartSummary['total'],
            'shipping_address' => sanitize_string($shippingData['shipping_address']),
            'shipping_city' => sanitize_string($shippingData['shipping_city']),
            'shipping_barangay' => sanitize_string($shippingData['shipping_barangay']),
            'shipping_zip' => sanitize_string($shippingData['shipping_zip']),
            'payment_method' => sanitize_string($shippingData['payment_method']),
            'payment_status' => 'pending',
            'notes' => sanitize_string($shippingData['notes'] ?? '')
        ];
        
        $orderId = db_insert($this->table, $orderData);
        
        if ($orderId) {
            // Transfer cart items to order
            $cartModel->transferToOrder($userId, $orderId);
            return $orderId;
        }
        
        return false;
    }
    
    public function getOrderWithItems($orderId, $userId = null) {
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM {$this->table} o
            INNER JOIN users u ON o.user_id = u.id
            WHERE o.id = :order_id
        ";
        
        $params = ['order_id' => $orderId];
        
        if ($userId) {
            $sql .= " AND o.user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $order = db_select_one($sql, $params);
        
        if (!$order) {
            return null;
        }
        
        // Get order items
        $itemsSql = "
            SELECT oi.*, p.name, p.image
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ";
        
        $order['items'] = db_select($itemsSql, ['order_id' => $orderId]);
        
        return $order;
    }
    
    public function getUserOrders($userId, $limit = 5, $offset = 0, $statuses = null) {
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email, da.city, da.barangay, da.street, da.zipcode 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
            WHERE o.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($statuses) {
            $placeholders = str_repeat('?,', count($statuses) - 1) . '?';
            $sql .= " AND o.status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }
        
        $sql .= " ORDER BY o.order_date DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add items to each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getItems($order['id']);
            $order['delivery_address'] = [
                'city' => $order['city'],
                'barangay' => $order['barangay'],
                'street' => $order['street'],
                'zipcode' => $order['zipcode']
            ];
        }
        
        return $orders;
    }
    
    public function getUserOrdersCount($userId, $statuses = null) {
        $sql = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
        $params = [$userId];
        
        if ($statuses) {
            $placeholders = str_repeat('?,', count($statuses) - 1) . '?';
            $sql .= " AND status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function updateStatus($orderId, $status, $notes = null) {
        // Debug: Log the update data
        error_log("Updating order {$orderId} to status: {$status}");
        
        // Prepare the update data
        $updateData = ['status' => $status];
        
        // Set dates based on status
        switch ($status) {
            case 'shipped':
                $updateData['shipped_date'] = date('Y-m-d H:i:s');
                break;
            case 'delivered':
                $updateData['delivery_date'] = date('Y-m-d H:i:s');
                break;
        }
        
        // Build the SQL query dynamically based on the update data
        $setParts = [];
        $params = [];
        foreach ($updateData as $key => $value) {
            $setParts[] = "{$key} = ?";
            $params[] = $value;
        }
        $params[] = $orderId; // Add orderId for WHERE clause
        
        $sql = "UPDATE orders SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            error_log("Status update failed: " . json_encode($stmt->errorInfo()));
        }
        
        // Handle stock restoration for cancelled orders
        if ($status === 'cancelled') {
            $this->restoreStock($orderId);
        }
        
        return $result;
    }
    
    public function restoreStock($orderId) {
        $items = db_select("SELECT * FROM order_items WHERE order_id = :order_id", ['order_id' => $orderId]);
        
        $productModel = new Product();
        foreach ($items as $item) {
            $productModel->restoreStock($item['product_id'], $item['quantity']);
        }
    }
    
    public function getOrderStats() {
        return [
            'total' => $this->count(),
            'pending' => $this->count(['status' => 'pending']),
            'processing' => $this->count(['status' => 'processing']),
            'shipped' => $this->count(['status' => 'shipped']),
            'delivered' => $this->count(['status' => 'delivered']),
            'cancelled' => $this->count(['status' => 'cancelled']),
            'total_revenue' => $this->getTotalRevenue()
        ];
    }
    
    public function getTotalRevenue($status = ['delivered']) {
        $statusList = "'" . implode("','", $status) . "'";
        $sql = "SELECT SUM(total) as revenue FROM {$this->table} WHERE status IN ({$statusList})";
        $result = db_select_one($sql);
        return (float)($result['revenue'] ?? 0);
    }
    
    public function getRecentOrders($limit = 10) {
        $sql = "
            SELECT o.*, u.first_name, u.last_name
            FROM {$this->table} o
            INNER JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT {$limit}
        ";
        
        return db_select($sql);
    }
    
    public function getSalesData($period = '30_days') {
        switch ($period) {
            case '7_days':
                $interval = 'INTERVAL 7 DAY';
                $format = '%Y-%m-%d';
                break;
            case '30_days':
                $interval = 'INTERVAL 30 DAY';
                $format = '%Y-%m-%d';
                break;
            case '12_months':
                $interval = 'INTERVAL 12 MONTH';
                $format = '%Y-%m';
                break;
            default:
                $interval = 'INTERVAL 30 DAY';
                $format = '%Y-%m-%d';
        }
        
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '{$format}') as period,
                COUNT(*) as order_count,
                SUM(total) as revenue
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), {$interval})
            AND status IN ('delivered', 'shipped')
            GROUP BY DATE_FORMAT(created_at, '{$format}')
            ORDER BY period ASC
        ";
        
        return db_select($sql);
    }
    
    public function getTopCustomers($limit = 10) {
        $sql = "
            SELECT 
                u.id, u.first_name, u.last_name, u.email,
                COUNT(o.id) as order_count,
                SUM(o.total) as total_spent
            FROM users u
            INNER JOIN {$this->table} o ON u.id = o.user_id
            WHERE o.status IN ('delivered', 'shipped')
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT {$limit}
        ";
        
        return db_select($sql);
    }
    
    public function canBeCancelled($orderId, $userId = null) {
        $conditions = ['id' => $orderId, 'status' => ['pending', 'processing']];
        
        if ($userId) {
            $conditions['user_id'] = $userId;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id = :id AND status IN ('pending', 'processing')";
        $params = ['id' => $orderId];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $result = db_select_one($sql, $params);
        return $result['count'] > 0;
    }
    
    public function cancelOrder($orderId, $userId = null, $reason = null) {
        if (!$this->canBeCancelled($orderId, $userId)) {
            return false;
        }
        
        return $this->updateStatus($orderId, 'cancelled');
    }

    // Get all orders with pagination for admin view
    public function getAdminPaginated($limit, $offset, $query = null, $status = null, $startDate = null, $endDate = null) {
        // Changed: Added search, status, and date range filters
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email, da.city, da.barangay, da.street, da.zipcode 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
                WHERE 1"; // Start with WHERE 1 to easily append conditions

        $params = [];

        // Add search query filter
        if ($query) {
            $sql .= " AND (o.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add status filter
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        // Add date range filter
        if ($startDate) {
            $sql .= " AND o.order_date >= ?";
            $params[] = $startDate . ' 00:00:00'; // Include start of the day
        }
        if ($endDate) {
            $sql .= " AND o.order_date <= ?";
            $params[] = $endDate . ' 23:59:59'; // Include end of the day
        }

        $sql .= " ORDER BY o.order_date DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Manually add delivery address details if needed (already done in getAll, replicating here)
        foreach ($orders as &$order) {
            // Ensure delivery_address is structured as an array
            $order['delivery_address'] = [
                'city' => $order['city'] ?? null,
                'barangay' => $order['barangay'] ?? null,
                'street' => $order['street'] ?? null,
                'zipcode' => $order['zipcode'] ?? null
            ];
             // Unset redundant keys if necessary (optional)
             unset($order['city'], $order['barangay'], $order['street'], $order['zipcode']);

            // Fetch order items
            $order['items'] = $this->getItems($order['id']);
        }

        return $orders;
    }

    // Get the total count of all orders with search and optional filters for admin view
    public function getAdminTotalCount($query = null, $status = null, $startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(*) 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
                WHERE 1"; // Start with WHERE 1 to easily append conditions

        $params = [];

        // Add search query filter
        if ($query) {
            $sql .= " AND (o.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add status filter
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        // Add date range filter
        if ($startDate) {
            $sql .= " AND o.order_date >= ?";
            $params[] = $startDate . ' 00:00:00'; // Include start of the day
        }
        if ($endDate) {
            $sql .= " AND o.order_date <= ?";
            $params[] = $endDate . ' 23:59:59'; // Include end of the day
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
?>
