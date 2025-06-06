<?php
/**
 * Pawfect Pet Shop - Database Helper Functions
 * Provides database connection and query functions
 */

// Database connection
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    return $pdo;
}

// Execute a query and return results
function db_select($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database select error: " . $e->getMessage());
        return [];
    }
}

// Execute a query and return single result
function db_select_one($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log("Database select one error: " . $e->getMessage());
        return null;
    }
}

// Insert data and return last insert ID
function db_insert($table, $data) {
    try {
        $pdo = get_db_connection();
        
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $pdo->lastInsertId();
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Database insert error: " . $e->getMessage());
        return false;
    }
}

// Update data
function db_update($table, $data, $where, $whereParams = []) {
    $params = [];
    try {
        $pdo = get_db_connection();
        
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = $pdo->prepare($sql);
        
        // Merge data and where parameters
        $params = array_merge($data, $whereParams);
        
        return $stmt->execute($params);
    } catch (PDOException $e) {
        // Log the SQL query, parameters, and the specific PDO error message
        error_log("Database update error for table {$table}: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params));
        return false;
    }
}

// Delete data
function db_delete($table, $where, $params = []) {
    try {
        $pdo = get_db_connection();
        
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Database delete error: " . $e->getMessage());
        return false;
    }
}

// Execute any query
function db_query($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

// Begin transaction
function db_begin_transaction() {
    try {
        $pdo = get_db_connection();
        return $pdo->beginTransaction();
    } catch (PDOException $e) {
        error_log("Database transaction begin error: " . $e->getMessage());
        return false;
    }
}

// Commit transaction
function db_commit() {
    try {
        $pdo = get_db_connection();
        return $pdo->commit();
    } catch (PDOException $e) {
        error_log("Database commit error: " . $e->getMessage());
        return false;
    }
}

// Rollback transaction
function db_rollback() {
    try {
        $pdo = get_db_connection();
        return $pdo->rollBack();
    } catch (PDOException $e) {
        error_log("Database rollback error: " . $e->getMessage());
        return false;
    }
}

// Get last insert ID
function db_last_insert_id() {
    try {
        $pdo = get_db_connection();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database last insert ID error: " . $e->getMessage());
        return false;
    }
}

// Check if table exists
function db_table_exists($table) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SHOW TABLES LIKE :table");
        $stmt->execute(['table' => $table]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Database table exists check error: " . $e->getMessage());
        return false;
    }
}

// Get table columns
function db_get_columns($table) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DESCRIBE {$table}");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database get columns error: " . $e->getMessage());
        return [];
    }
}
?>
