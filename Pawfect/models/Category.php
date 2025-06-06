<?php
/**
 * Pawfect Pet Shop - Category Model
 * Handles product and pet types
 */

class Category {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        // Get unique types from both products and pets
        $sql = "SELECT DISTINCT type FROM products WHERE type IS NOT NULL AND type != ''
                UNION
                SELECT DISTINCT type FROM pets WHERE type IS NOT NULL AND type != ''
                ORDER BY type ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getActiveCategories() {
        return $this->getAll(); // All types are active
    }
    
    public function getFeaturedCategories($limit = 6) {
        // Get types with most products/pets
        $sql = "SELECT type, COUNT(*) as count FROM (
                    SELECT type FROM products WHERE type IS NOT NULL AND type != ''
                    UNION ALL
                    SELECT type FROM pets WHERE type IS NOT NULL AND type != ''
                ) combined
                GROUP BY type
                ORDER BY count DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getParentCategories() {
        return $this->getAll(); // All types are parent categories
    }
    
    public function getSubCategories($parentId) {
        return []; // No subcategories in type-based system
    }
    
    public function getCategoryWithProducts($type) {
        $productModel = new Product();
        $products = $productModel->getByType($type);
        
        return [
            'category' => ['name' => ucfirst($type), 'type' => $type],
            'products' => $products
        ];
    }
    
    public function getCategoryStats() {
        $sql = "SELECT 
                COUNT(DISTINCT type) as total,
                COUNT(DISTINCT CASE WHEN type IN ('dogs', 'cats') THEN type END) as pets,
                COUNT(DISTINCT CASE WHEN type NOT IN ('dogs', 'cats') THEN type END) as products
                FROM (
                    SELECT type FROM products WHERE type IS NOT NULL AND type != ''
                    UNION
                    SELECT type FROM pets WHERE type IS NOT NULL AND type != ''
                ) combined";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }
    
    public function getCategoriesWithProductCount() {
        $sql = "SELECT 
                type,
                COUNT(*) as product_count
                FROM (
                    SELECT type FROM products WHERE type IS NOT NULL AND type != ''
                    UNION ALL
                    SELECT type FROM pets WHERE type IS NOT NULL AND type != ''
                ) combined
                GROUP BY type
                ORDER BY type ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function generateSlug($name) {
        return strtolower(str_replace(' ', '-', $name));
    }
    
    public function isSlugTaken($slug) {
        return false; // Not using slugs in type-based system
    }
}
?>
