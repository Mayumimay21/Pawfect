<?php
/**
 * Pawfect Pet Shop - Category Model
 * Handles product category data and operations
 */

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'description', 'slug', 'image', 'parent_id', 
        'sort_order', 'featured', 'status', 'meta_title', 'meta_description'
    ];
    
    public function getActiveCategories() {
        return $this->where(['status' => 'active'], 'sort_order ASC, name ASC');
    }
    
    public function getFeaturedCategories($limit = 6) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' AND featured = 1 ORDER BY sort_order ASC, name ASC LIMIT {$limit}";
        return db_select($sql);
    }
    
    public function getParentCategories() {
        return $this->where(['parent_id' => null, 'status' => 'active'], 'sort_order ASC, name ASC');
    }
    
    public function getSubCategories($parentId) {
        return $this->where(['parent_id' => $parentId, 'status' => 'active'], 'sort_order ASC, name ASC');
    }
    
    public function getCategoryWithProducts($id) {
        $category = $this->find($id);
        
        if (!$category) {
            return null;
        }
        
        $productModel = new Product();
        $products = $productModel->getProductsByCategory($id);
        
        return [
            'category' => $category,
            'products' => $products
        ];
    }
    
    public function getCategoryStats() {
        return [
            'total' => $this->count(),
            'active' => $this->count(['status' => 'active']),
            'inactive' => $this->count(['status' => 'inactive']),
            'featured' => $this->count(['featured' => 1, 'status' => 'active']),
            'parent_categories' => $this->count(['parent_id' => null, 'status' => 'active'])
        ];
    }
    
    public function getCategoriesWithProductCount() {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ";
        
        return db_select($sql);
    }
    
    public function generateSlug($name, $excludeId = null) {
        $slug = str_slug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        do {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";
            $params = ['slug' => $slug];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $excludeId;
            }
            
            $result = db_select_one($sql, $params);
            
            if ($result['count'] > 0) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        } while ($result['count'] > 0);
        
        return $slug;
    }
    
    public function isSlugTaken($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = db_select_one($sql, $params);
        return $result['count'] > 0;
    }
    
    public function updateSortOrder($categoryId, $sortOrder) {
        return $this->update($categoryId, ['sort_order' => $sortOrder]);
    }
    
    public function getTopSellingCategories($limit = 10) {
        $sql = "
            SELECT c.*, SUM(oi.quantity) as total_sold
            FROM {$this->table} c
            INNER JOIN products p ON c.id = p.category_id
            INNER JOIN order_items oi ON p.id = oi.product_id
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE c.status = 'active' AND o.status IN ('delivered', 'shipped')
            GROUP BY c.id
            ORDER BY total_sold DESC
            LIMIT {$limit}
        ";
        
        return db_select($sql);
    }
}
?>
