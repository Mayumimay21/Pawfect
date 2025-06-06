<?php
/**
 * Pawfect Pet Shop - Base Controller Class
 * Provides common functionality for all controllers
 */

class Controller {
    
    /**
     * Load a view file
     */
    protected function view($viewPath, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Convert dot notation to file path
        $viewFile = 'views/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file not found: " . $viewFile);
        }
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect($url, $message = null, $type = 'success') {
        if ($message) {
            $_SESSION[$type] = $message;
        }
        
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get request input
     */
    protected function input($key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Validate required fields
     */
    protected function validate($rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }
            
            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = ucfirst($field) . ' must be a number';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $min . ' characters';
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = $matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $max . ' characters';
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!isLoggedIn()) {
            $this->redirect('/login', 'Please login to continue', 'error');
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin() {
        $this->requireAuth();
        
        if (!isAdmin()) {
            $this->redirect('/', 'Access denied', 'error');
        }
    }
    
    /**
     * Get current user
     */
    protected function user() {
        return getCurrentUser();
    }
    
    /**
     * Flash message to session
     */
    protected function flash($message, $type = 'success') {
        $_SESSION[$type] = $message;
    }

    /**
     * Generate URL helper
     */
    protected function url($path) {
        return BASE_URL . $path;
    }
}
?>
