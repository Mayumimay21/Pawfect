<?php
/**
 * Pawfect Pet Shop - Base Model Class
 * Provides common database operations for all models
 */

class Model {
    protected $table;
    protected $fillable = [];
    protected $primaryKey = 'id';
    
    public function __construct() {
        if (!$this->table) {
            // Auto-generate table name from class name if not set
            $className = get_class($this);
            $this->table = strtolower($className) . 's';
        }
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return db_select_one($sql, ['id' => $id]);
    }
    
    /**
     * Alias for find method
     */
    public function findById($id) {
        return $this->find($id);
    }
    
    /**
     * Find record by specific column
     */
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return db_select_one($sql, ['value' => $value]);
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return db_select($sql);
    }
    
    /**
     * Get records with conditions
     */
    public function where($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $v) {
                        $placeholder = $column . '_' . $i;
                        $placeholders[] = ':' . $placeholder;
                        $params[$placeholder] = $v;
                    }
                    $whereClause[] = "{$column} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $whereClause[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return db_select($sql, $params);
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $v) {
                        $placeholder = $column . '_' . $i;
                        $placeholders[] = ':' . $placeholder;
                        $params[$placeholder] = $v;
                    }
                    $whereClause[] = "{$column} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $whereClause[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $result = db_select_one($sql, $params);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        // Filter data to only include fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        // Add timestamps
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        return db_insert($this->table, $data);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        // Filter data to only include fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        // Add updated timestamp
        $data['updated_at'] = now();
        
        return db_update($this->table, $data, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        return db_delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    /**
     * Search records
     */
    public function search($query, $columns = [], $limit = 20) {
        if (empty($columns)) {
            return [];
        }
        
        $searchConditions = [];
        $params = [];
        
        foreach ($columns as $i => $column) {
            $paramName = 'search_' . $i;
            $searchConditions[] = "{$column} LIKE :{$paramName}";
            $params[$paramName] = "%{$query}%";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' OR ', $searchConditions);
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return db_select($sql, $params);
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null) {
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $this->count($conditions);
        
        // Get data
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $v) {
                        $placeholder = $column . '_' . $i;
                        $placeholders[] = ':' . $placeholder;
                        $params[$placeholder] = $v;
                    }
                    $whereClause[] = "{$column} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $whereClause[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = db_select($sql, $params);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'has_pages' => $total > $perPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get first record matching conditions
     */
    public function first($conditions = [], $orderBy = null) {
        $results = $this->where($conditions, $orderBy, 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Check if record exists
     */
    public function exists($conditions) {
        return $this->count($conditions) > 0;
    }
}
?>
