<?php
class Cart {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function addItem($userId, $productId, $quantity = 1) {
        try {
            // Debug log
            error_log("Cart::addItem - User ID: $userId, Product ID: $productId, Quantity: $quantity");
            
        // Check if item already exists
        $stmt = $this->pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
                error_log("Updating existing cart item");
            $stmt = $this->pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
                $result = $stmt->execute([$quantity, $userId, $productId]);
                error_log("Update result: " . ($result ? 'true' : 'false'));
                return $result;
        } else {
            // Add new item
                error_log("Adding new cart item");
            $stmt = $this->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $result = $stmt->execute([$userId, $productId, $quantity]);
                error_log("Insert result: " . ($result ? 'true' : 'false'));
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error in Cart::addItem: " . $e->getMessage());
            return false;
        }
    }
    
    public function getItems($userId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, p.name, p.price, p.product_image, p.stock_quantity, p.type 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_archived = FALSE
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }
        
        $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$quantity, $userId, $productId]);
    }
    
    public function removeItem($userId, $productId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
    
    public function clearCart($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function getTotal($userId) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_archived = FALSE
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getItemCount($userId) {
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get cart summary including items, subtotal, tax, and shipping cost
     * @param int $userId User ID
     * @return array Cart summary
     */
    public function getCartSummary($userId) {
        $items = $this->getItems($userId);
        $subtotal = $this->getTotal($userId);
        $tax = $subtotal * 0.12; // 12% tax
        $shippingCost = $this->calculateShippingCost($items);
        $total = $subtotal + $tax + $shippingCost;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'total' => $total
        ];
    }

    /**
     * Validate cart items for stock availability and other conditions
     * @param int $userId User ID
     * @return array Array of validation issues, empty if valid
     */
    public function validateCart($userId) {
        $issues = [];
        $items = $this->getItems($userId);

        if (empty($items)) {
            $issues[] = 'Cart is empty';
            return $issues;
        }

        foreach ($items as $item) {
            // Check stock availability
            if ($item['stock_quantity'] < $item['quantity']) {
                $issues[] = "Insufficient stock for {$item['name']} (Available: {$item['stock_quantity']})";
            }

            // Check if product is archived
            if ($item['is_archived']) {
                $issues[] = "Product {$item['name']} is no longer available";
            }
        }

        return $issues;
    }

    /**
     * Transfer cart items to order
     * @param int $userId User ID
     * @param int $orderId Order ID
     * @return bool Success status
     */
    public function transferToOrder($userId, $orderId) {
        try {
            $this->pdo->beginTransaction();

            $items = $this->getItems($userId);
            $orderModel = new Order();
            $productModel = new Product();

            foreach ($items as $item) {
                // Verify stock availability again before transfer
                $product = $productModel->getById($item['product_id']);
                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product ID {$item['product_id']}");
                }

                // Add item to order
                $orderModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);

                // Update product stock
                $stmt = $this->pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE id = ? AND stock_quantity >= ?
                ");
                $result = $stmt->execute([
                    $item['quantity'], 
                    $item['product_id'], 
                    $item['quantity']
                ]);
                
                if (!$result || $stmt->rowCount() === 0) {
                    throw new Exception("Failed to update stock for product ID {$item['product_id']}");
                }
            }

            // Clear the cart
            $this->clearCart($userId);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error transferring cart to order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate shipping cost based on items
     * @param array $items Cart items
     * @return float Shipping cost
     */
    private function calculateShippingCost($items) {
        // Base shipping cost
        $baseCost = 50.00;
        
        // Additional cost per item
        $perItemCost = 10.00;
        
        // Calculate total items
        $totalItems = array_sum(array_column($items, 'quantity'));
        
        // Calculate total shipping cost
        $shippingCost = $baseCost + ($perItemCost * $totalItems);
        
        // Free shipping for orders over 1000
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));
        
        return $subtotal >= 1000 ? 0 : $shippingCost;
    }
}
?>
