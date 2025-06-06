<?php
/**
 * Pawfect Pet Shop - Pet Order Model
 * Handles pet adoption order data and operations
 */

class PetOrder extends Model {
    protected $table = 'pet_orders';
    protected $fillable = [
        'order_number', 'user_id', 'status', 'total_amount', 'payment_method',
        'delivery_address_id', 'notes', 'admin_notes', 'cancelled_at',
        'approved_date', 'created_at', 'updated_at'
    ];

    protected $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function createOrder($userId, $petId, $totalAmount, $paymentMethod = 'COD', $deliveryAddressId = null, $notes = null) {
        try {
            if (!$this->pdo) {
                throw new Exception("Database connection not available");
            }

            $this->pdo->beginTransaction();
            
            // Generate unique order number
            $orderNumber = 'PO-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create the order with pending status
            $stmt = $this->pdo->prepare("
                INSERT INTO pet_orders (
                    order_number, user_id, status, total_amount, payment_method,
                    delivery_address_id, notes, created_at
                ) VALUES (?, ?, 'pending', ?, ?, ?, ?, NOW())
            ");
            
            try {
                $result = $stmt->execute([
                    $orderNumber, $userId, $totalAmount, $paymentMethod,
                    $deliveryAddressId, $notes
                ]);

                if (!$result) {
                    throw new Exception("Failed to insert order: " . implode(", ", $stmt->errorInfo()));
                }
            } catch (PDOException $e) {
                throw new Exception("Database error while inserting order: " . $e->getMessage());
            }
            
            $orderId = $this->pdo->lastInsertId();
            if (!$orderId) {
                throw new Exception("Failed to get last insert ID after order creation");
            }
            
            // Add the pet to the order
            $stmt = $this->pdo->prepare("
                INSERT INTO pet_order_items (order_id, pet_id, price) 
                VALUES (?, ?, ?)
            ");
            
            try {
                $result = $stmt->execute([$orderId, $petId, $totalAmount]);
                
                if (!$result) {
                    throw new Exception("Failed to insert order item: " . implode(", ", $stmt->errorInfo()));
                }
            } catch (PDOException $e) {
                throw new Exception("Database error while inserting order item: " . $e->getMessage());
            }
            
            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error creating pet order: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw the exception to be caught by the controller
        }
    }
    
    public function getByUser($userId, $limit = null, $offset = 0) {
        $sql = "
            SELECT po.*, p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                   u.first_name, u.last_name, u.email, u.phone,
                   da.city, da.barangay, da.street, da.zipcode
            FROM pet_orders po
            JOIN pet_order_items poi ON po.id = poi.order_id
            JOIN pets p ON poi.pet_id = p.id
            JOIN users u ON po.user_id = u.id
            LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
            WHERE po.user_id = ?
            ORDER BY po.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT po.*, u.first_name, u.last_name, u.email, u.phone,
                   p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                   da.city, da.barangay, da.street, da.zipcode
            FROM pet_orders po
            JOIN users u ON po.user_id = u.id
            JOIN pet_order_items poi ON po.id = poi.order_id
            JOIN pets p ON poi.pet_id = p.id
            LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
            WHERE po.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "
            SELECT po.*, u.first_name, u.last_name, u.email,
                   p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                   da.city, da.barangay, da.street, da.zipcode
            FROM pet_orders po
            JOIN users u ON po.user_id = u.id
            JOIN pet_order_items poi ON po.id = poi.order_id
            JOIN pets p ON poi.pet_id = p.id
            LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
            ORDER BY po.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    public function updateStatus($orderId, $status, $adminNotes = null) {
        try {
            if (!$this->pdo) {
                throw new Exception("Database connection not available");
            }

            $this->pdo->beginTransaction();
            
            // Update order status
            $stmt = $this->pdo->prepare("
                UPDATE pet_orders 
                SET status = ?, 
                    updated_at = NOW(),
                    notes = ?,
                    cancelled_at = CASE WHEN ? = 'cancelled' THEN NOW() ELSE NULL END,
                    approved_date = CASE WHEN ? = 'approved' THEN NOW() ELSE NULL END
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$status, $adminNotes, $status, $status, $orderId]);
            
            if (!$result) {
                throw new Exception("Failed to update order status: " . implode(", ", $stmt->errorInfo()));
            }

            if ($stmt->rowCount() === 0) {
                throw new Exception("No order found with ID: " . $orderId);
            }
            
            // Get the pet ID for this order
            $stmt = $this->pdo->prepare("
                SELECT poi.pet_id
                FROM pet_orders po
                JOIN pet_order_items poi ON po.id = poi.order_id
                WHERE po.id = ?
            ");
            $stmt->execute([$orderId]);
            $orderDetails = $stmt->fetch();
            
            if (!$orderDetails) {
                throw new Exception("No pet found for order ID: " . $orderId);
            }
            
            // Update pet status based on order status
            if ($status === 'approved') {
                $stmt = $this->pdo->prepare("
                    UPDATE pets 
                    SET is_adopted = TRUE,
                        adopted_by_user_id = (SELECT user_id FROM pet_orders WHERE id = ?)
                    WHERE id = ?
                ");
                $result = $stmt->execute([$orderId, $orderDetails['pet_id']]);
                
                if (!$result) {
                    throw new Exception("Failed to update pet status to adopted: " . implode(", ", $stmt->errorInfo()));
                }
            }
            else if ($status === 'cancelled' || $status === 'rejected') {
                $stmt = $this->pdo->prepare("
                    UPDATE pets 
                    SET is_adopted = FALSE,
                        adopted_by_user_id = NULL
                    WHERE id = ?
                ");
                $result = $stmt->execute([$orderDetails['pet_id']]);
                
                if (!$result) {
                    throw new Exception("Failed to update pet status to available: " . implode(", ", $stmt->errorInfo()));
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error updating pet order status: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function canBeCancelled($orderId, $userId = null) {
        $sql = "SELECT status FROM pet_orders WHERE id = ?";
        $params = [$orderId];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();
        
        return $order && $order['status'] === 'pending';
    }
    
    public function cancelOrder($orderId, $userId = null, $reason = null) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE pet_orders SET status = 'cancelled', notes = ?, cancelled_at = NOW() WHERE id = ?";
            $params = [$reason, $orderId];
            
            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Reset pet adoption status
            $stmt = $this->pdo->prepare("
                UPDATE pets p
                JOIN pet_order_items poi ON p.id = poi.pet_id
                SET p.is_adopted = FALSE
                WHERE poi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error cancelling pet order: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAdminPaginated($limit, $offset, $query = null, $status = null) {
        $sql = "SELECT po.*, u.first_name, u.last_name, u.email, u.phone,
                       p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                       da.city, da.barangay, da.street, da.zipcode
                FROM pet_orders po 
                JOIN users u ON po.user_id = u.id 
                JOIN pet_order_items poi ON po.id = poi.order_id
                JOIN pets p ON poi.pet_id = p.id
                LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
                WHERE 1=1";
        $params = [];

        if ($query) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? 
                          OR po.order_number LIKE ? OR p.name LIKE ?)";
            $searchTerm = "%$query%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status) {
            $sql .= " AND po.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY po.created_at DESC";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAdminTotalCount($query = null, $status = null) {
        $sql = "SELECT COUNT(DISTINCT po.id) as total 
                FROM pet_orders po 
                JOIN users u ON po.user_id = u.id 
                JOIN pet_order_items poi ON po.id = poi.order_id
                JOIN pets p ON poi.pet_id = p.id
                WHERE 1=1";
        $params = [];

        if ($query) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? 
                          OR po.order_number LIKE ? OR p.name LIKE ?)";
            $searchTerm = "%$query%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status) {
            $sql .= " AND po.status = ?";
            $params[] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getStats() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_orders
            FROM pet_orders
        ");
        return $stmt->fetch();
    }
    
    public function getRecentOrders($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT po.*, u.first_name, u.last_name, u.email,
                   p.name as pet_name, p.pet_image as pet_image,
                   da.city, da.barangay, da.street, da.zipcode
            FROM pet_orders po
            JOIN users u ON po.user_id = u.id
            JOIN pet_order_items poi ON po.id = poi.order_id
            JOIN pets p ON poi.pet_id = p.id
            LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
            ORDER BY po.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getOrderWithPets($orderId, $userId = null) {
        $sql = "SELECT po.*, u.first_name, u.last_name, u.email, u.phone,
                       p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                       da.city, da.barangay, da.street, da.zipcode
                FROM pet_orders po
                JOIN users u ON po.user_id = u.id
                JOIN pet_order_items poi ON po.id = poi.order_id
                JOIN pets p ON poi.pet_id = p.id
                LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
                WHERE po.id = ?";
        
        $params = [$orderId];
        
        if ($userId) {
            $sql .= " AND po.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getUserOrders($userId, $limit = null, $offset = 0) {
        $sql = "SELECT po.*, u.first_name, u.last_name, u.email, u.phone,
                       p.name as pet_name, p.pet_image as pet_image, p.type, p.breed,
                       da.city, da.barangay, da.street, da.zipcode
                FROM pet_orders po
                JOIN users u ON po.user_id = u.id
                JOIN pet_order_items poi ON po.id = poi.order_id
                JOIN pets p ON poi.pet_id = p.id
                LEFT JOIN delivery_addresses da ON po.delivery_address_id = da.id
                WHERE po.user_id = ?
                ORDER BY po.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }

    public function getUserOrdersCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM pet_orders
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'];
    }
} 