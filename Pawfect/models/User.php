<?php
/**
 * Pawfect Pet Shop - User Model
 * Handles user data and authentication
 */

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'delivery_addresses', 'password',
        'city', 'barangay', 'zip_code', 'date_of_birth', 'avatar',
        'email_verified_at', 'status', 'role'
    ];
    
    protected $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $hashedPassword, $data['phone'], null]);
    }
    
    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?,  avatar = ? WHERE id = ?");
        return $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $data['phone'],  $data['avatar'] ?? null, $id]);
    }
    
    public function updateRole($id, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }
    
    public function banUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_banned = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function unbanUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_banned = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Set default values
        $data['role'] = $data['role'] ?? 'user';
        $data['status'] = $data['status'] ?? 'active';
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        return db_insert($this->table, $data);
    }
    
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    public function verifyPasswordOld($userId, $password) {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function verifyEmail($userId) {
        return $this->update($userId, ['email_verified_at' => now()]);
    }
    
    public function isEmailVerified($userId) {
        $user = $this->find($userId);
        return $user && $user['email_verified_at'] !== null;
    }
    
    public function getUserOrders($userId, $limit = null) {
        $sql = "
            SELECT o.*, COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return db_select($sql, ['user_id' => $userId]);
    }
    
    public function getUserAdoptionApplications($userId) {
        $sql = "
            SELECT aa.*, p.name as pet_name, p.image as pet_image
            FROM adoption_applications aa
            INNER JOIN pets p ON aa.pet_id = p.id
            WHERE aa.user_id = :user_id
            ORDER BY aa.created_at DESC
        ";
        
        return db_select($sql, ['user_id' => $userId]);
    }
    
    public function getUserStats($userId) {
        $orders = $this->getUserOrders($userId);
        $applications = $this->getUserAdoptionApplications($userId);
        
        $totalSpent = 0;
        foreach ($orders as $order) {
            if (in_array($order['status'], ['delivered', 'shipped'])) {
                $totalSpent += $order['total'];
            }
        }
        
        return [
            'total_orders' => count($orders),
            'total_spent' => $totalSpent,
            'pending_orders' => count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })),
            'adoption_applications' => count($applications),
            'approved_adoptions' => count(array_filter($applications, function($a) { return $a['status'] === 'approved'; }))
        ];
    }
    
    public function getRecentCustomers($limit = 10) {
        return $this->where(['role' => 'user'], 'created_at DESC', $limit);
    }
    
    public function getTopCustomers($limit = 10) {
        $sql = "
            SELECT u.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent
            FROM {$this->table} u
            INNER JOIN orders o ON u.id = o.user_id
            WHERE u.role = 'user' AND o.status IN ('delivered', 'shipped')
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT {$limit}
        ";
        
        return db_select($sql);
    }
    
    public function getUsersByRole($role) {
        return $this->where(['role' => $role], 'created_at DESC');
    }
    
    public function searchUsers($query, $limit = 20) {
        return $this->search($query, ['first_name', 'last_name', 'email'], $limit);
    }
    
    public function updateLastLogin($userId) {
        return $this->update($userId, ['last_login_at' => now()]);
    }
    
    public function deactivateUser($userId) {
        return $this->update($userId, ['status' => 'inactive']);
    }
    
    public function activateUser($userId) {
        return $this->update($userId, ['status' => 'active']);
    }
    
    public function getUserProfile($userId) {
        $user = $this->find($userId);
        
        if (!$user) {
            return null;
        }
        
        // Remove sensitive data
        unset($user['password']);
        
        // Add additional profile data
        $user['stats'] = $this->getUserStats($userId);
        $user['recent_orders'] = $this->getUserOrders($userId, 5);
        $user['adoption_applications'] = $this->getUserAdoptionApplications($userId);
        
        return $user;
    }
    
    public function updateProfile($userId, $data) {
        // Remove sensitive fields that shouldn't be updated via profile
        unset($data['password'], $data['role'], $data['email_verified_at']);
        
        return $this->update($userId, $data);
    }
    
    public function generatePasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->update($userId, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);
        
        return $token;
    }
    
    public function verifyPasswordResetToken($token) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE password_reset_token = :token
            AND password_reset_expires > NOW()
            LIMIT 1
        ";
        
        return db_select_one($sql, ['token' => $token]);
    }
    
    public function clearPasswordResetToken($userId) {
        return $this->update($userId, [
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }

    public function getPrimaryDeliveryAddress($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM delivery_addresses WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function findOrCreateDeliveryAddress($userId, $city, $barangay, $street, $zipcode) {
        try {
            $this->pdo->beginTransaction();

            // Check if address already exists for the user
            $stmt = $this->pdo->prepare("SELECT id FROM delivery_addresses WHERE user_id = ? AND city = ? AND barangay = ? AND street = ? AND zipcode = ? LIMIT 1");
            $stmt->execute([$userId, $city, $barangay, $street, $zipcode]);
            $existingAddress = $stmt->fetch();

            if ($existingAddress) {
                $this->pdo->commit();
                return $existingAddress['id'];
            }

            // Create new address
            $stmt = $this->pdo->prepare("INSERT INTO delivery_addresses (user_id, city, barangay, street, zipcode) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt->execute([$userId, $city, $barangay, $street, $zipcode])) {
                throw new Exception("Failed to create delivery address");
            }

            $addressId = $this->pdo->lastInsertId();
            if (!$addressId) {
                throw new Exception("Failed to get delivery address ID");
            }

            $this->pdo->commit();
            return $addressId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in findOrCreateDeliveryAddress: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    // Get all users with pagination for admin view
    public function getAdminPaginated($limit, $offset, $query = null, $role = null) {
        $sql = "SELECT * FROM users WHERE 1"; // Start with WHERE 1 to easily append conditions
        $params = [];

        // Add search query filter (by name or email)
        if ($query) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add role filter
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get the total count of all users with search and optional role filter for admin view
    public function getAdminTotalCount($query = null, $role = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE 1"; // Start with WHERE 1
        $params = [];

        // Add search query filter (by name or email)
        if ($query) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add role filter
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
?>
