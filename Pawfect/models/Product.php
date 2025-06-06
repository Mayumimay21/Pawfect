<?php
class Product {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM products WHERE is_archived = FALSE ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE type = ? AND is_archived = FALSE ORDER BY id DESC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO products (name, product_image, stock_quantity, type, price, description) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['product_image'], $data['stock_quantity'], $data['type'], $data['price'], $data['description']]);
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE products SET name = ?, product_image = ?, stock_quantity = ?, type = ?, price = ?, description = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['product_image'], $data['stock_quantity'], $data['type'], $data['price'], $data['description'], $id]);
    }
    
    public function archive($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_archived = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function restore($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_archived = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getArchived() {
        $stmt = $this->pdo->query("SELECT * FROM products WHERE is_archived = TRUE ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function updateStock($id, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        return $stmt->execute([$quantity, $id]);
    }
    
    public function restoreStock($id, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        return $stmt->execute([$quantity, $id]);
    }
    
    public function getStats() {
        $stmt = $this->pdo->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN type = 'accessories' THEN 1 ELSE 0 END) as accessories,
            SUM(CASE WHEN type = 'foods' THEN 1 ELSE 0 END) as foods,
            SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
            FROM products WHERE is_archived = FALSE");
        return $stmt->fetch();
    }

    public function search($query, $columns = ['name', 'description'], $limit = 20)
    {
        if (empty($columns) || empty($query)) {
            return [];
        }

        $searchConditions = [];
        $params = [];

        foreach ($columns as $i => $column) {
            $paramName = 'search_' . $i;
            $searchConditions[] = "{$column} LIKE :{$paramName}";
            $params[$paramName] = "%{$query}%";
        }

        $sql = "SELECT * FROM pets WHERE (" . implode(' OR ', $searchConditions) . ") AND is_archived = FALSE";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get products with pagination, search, and optional filters
    public function getPaginated($limit, $offset, $type = null, $minPrice = null, $maxPrice = null, $query = null, $stockStatus = null) {
        $sql = "SELECT * FROM products WHERE is_archived = FALSE";
        $params = [];

        // Add type filter
        if ($type && in_array($type, ['foods', 'accessories'])) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        // Add price range filter
        if ($minPrice !== null && $minPrice !== '') {
            $sql .= " AND price >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $sql .= " AND price <= ?";
            $params[] = $maxPrice;
        }

        // Add stock status filter
        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $sql .= " AND stock_quantity > 5";
                    break;
                case 'low_stock':
                    $sql .= " AND stock_quantity > 0 AND stock_quantity <= 5";
                    break;
                case 'out_of_stock':
                    $sql .= " AND stock_quantity = 0";
                    break;
            }
        }

        // Add search query filter
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Order by stock quantity (in-stock first) and then by ID
        $sql .= " ORDER BY CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END, id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get the total count of products with search and optional filters
    public function getTotalCount($type = null, $minPrice = null, $maxPrice = null, $query = null, $stockStatus = null) {
        $sql = "SELECT COUNT(*) FROM products WHERE is_archived = FALSE";
        $params = [];

        // Add type filter
        if ($type && in_array($type, ['foods', 'accessories'])) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        // Add price range filter
        if ($minPrice !== null && $minPrice !== '') {
            $sql .= " AND price >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $sql .= " AND price <= ?";
            $params[] = $maxPrice;
        }

        // Add stock status filter
        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $sql .= " AND stock_quantity > 5";
                    break;
                case 'low_stock':
                    $sql .= " AND stock_quantity > 0 AND stock_quantity <= 5";
                    break;
                case 'out_of_stock':
                    $sql .= " AND stock_quantity = 0";
                    break;
            }
        }

        // Add search query filter
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Get archived products with pagination for admin view
    public function getArchivedPaginated($limit, $offset) {
        $sql = "SELECT * FROM products WHERE is_archived = TRUE ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get the total count of archived products for admin view
    public function getArchivedTotalCount() {
        $sql = "SELECT COUNT(*) FROM products WHERE is_archived = TRUE";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn();
    }

    // Get all products with pagination, search, and optional archived filter for admin view
    public function getAdminPaginated($limit, $offset, $query = null, $isArchived = null) {
        $sql = "SELECT * FROM products WHERE 1";
        $params = [];

        // Add search query filter
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add archived filter
        if ($isArchived !== null && ($isArchived === '0' || $isArchived === '1')) {
             $sql .= " AND is_archived = ?";
             $params[] = (int)$isArchived; // Cast to integer
        }

        $sql .= " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get the total count of all products with search and optional archived filter for admin view
    public function getAdminTotalCount($query = null, $isArchived = null) {
        $sql = "SELECT COUNT(*) FROM products WHERE 1";
        $params = [];

        // Add search query filter
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        // Add archived filter
        if ($isArchived !== null && ($isArchived === '0' || $isArchived === '1')) {
            $sql .= " AND is_archived = ?";
            $params[] = (int)$isArchived; // Cast to integer
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopSoldProducts($limit = 4) {
        $sql = "SELECT p.*, SUM(oi.quantity) as total_sold 
                FROM products p 
                JOIN order_items oi ON p.id = oi.product_id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status IN ('delivered', 'shipped') 
                AND p.is_archived = FALSE 
                GROUP BY p.id 
                ORDER BY total_sold DESC 
                LIMIT " . (int)$limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopOutOfStockProducts($limit = 4) {
        $sql = "SELECT * FROM products WHERE stock_quantity < 5 ORDER BY stock_quantity ASC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
