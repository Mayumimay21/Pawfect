<?php
require_once 'models/Settings.php';
/**
 * Settings Helper Functions
 */
function get_setting($key, $default = null)
{
    static $settingsModel = null;

    try {
        if ($settingsModel === null) {
            $settingsModel = new Settings();
        }

        return $settingsModel->getSetting($key, $default);
    } catch (Exception $e) {
        error_log("Settings error: " . $e->getMessage());
        return $default;
    }
}

function set_setting($key, $value, $type = 'string', $group = 'general', $description = null)
{
    try {
        $settingsModel = new Settings();
        return $settingsModel->setSetting($key, $value, $type, $group, $description);
    } catch (Exception $e) {
        error_log("Settings error: " . $e->getMessage());
        return false;
    }
}

function update_settings($settings)
{
    try {
        $settingsModel = new Settings();
        return $settingsModel->updateSettings($settings);
    } catch (Exception $e) {
        error_log("Settings error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Base URL helper
 */
function get_base_url()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);

    return $protocol . '://' . $host . ($script !== '/' ? $script : '');
}

/**
 * Current timestamp
 */
function now()
{
    return date('Y-m-d H:i:s');
}

/**
 * Pet Helper Functions
 */
function format_pet_age($age, $unit)
{
    if ($age == 1) {
        return $age . ' ' . rtrim($unit, 's');
    }
    return $age . ' ' . $unit;
}

function get_pet_size_label($size)
{
    $labels = [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'extra_large' => 'Extra Large'
    ];

    return $labels[$size] ?? ucfirst($size);
}

/**
 * Order Helper Functions
 */
function generate_order_number()
{
    return 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

function generate_application_number()
{
    return 'APP-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Product Helper Functions
 */
function format_currency($amount, $currency = null)
{
    if ($currency === null) {
        $currency = get_setting('currency_symbol', '$');
    }

    return $currency . number_format((float)$amount, 2);
}

function calculate_discount_percentage($original, $sale)
{
    if ($original <= 0) return 0;
    return round((($original - $sale) / $original) * 100);
}

/**
 * String Helper Functions
 */
function str_limit($string, $limit = 100, $end = '...')
{
    if (mb_strlen($string) <= $limit) {
        return $string;
    }

    return mb_substr($string, 0, $limit) . $end;
}

function str_slug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

/**
 * File Helper Functions
 */
function format_file_size($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

function get_file_extension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function is_image_file($filename)
{
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    return in_array(get_file_extension($filename), $imageExtensions);
}

/**
 * Date Helper Functions
 */
function time_ago($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    if ($time < 31536000) return floor($time / 2592000) . ' months ago';

    return floor($time / 31536000) . ' years ago';
}

function format_date($date, $format = 'M j, Y')
{
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'M j, Y g:i A')
{
    return date($format, strtotime($datetime));
}

/**
 * Array Helper Functions
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) return $array;

    if (isset($array[$key])) return $array[$key];

    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }

    return $array;
}

function array_only($array, $keys)
{
    return array_intersect_key($array, array_flip((array) $keys));
}

function array_except($array, $keys)
{
    return array_diff_key($array, array_flip((array) $keys));
}

/**
 * Validation Helper Functions
 */
function sanitize_string($string)
{
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function sanitize_email($email)
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_phone($phone)
{
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return strlen($phone) >= 10;
}

/**
 * Cache Helper Functions (Simple file-based cache)
 */
function cache_get($key, $default = null)
{
    $cacheDir = 'cache';
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';

    if (!file_exists($cacheFile)) {
        return $default;
    }

    $data = unserialize(file_get_contents($cacheFile));

    if ($data['expires'] < time()) {
        unlink($cacheFile);
        return $default;
    }

    return $data['value'];
}

function cache_set($key, $value, $minutes = 60)
{
    $cacheDir = 'cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    $data = [
        'value' => $value,
        'expires' => time() + ($minutes * 60)
    ];

    return file_put_contents($cacheFile, serialize($data)) !== false;
}

function cache_forget($key)
{
    $cacheFile = 'cache/' . md5($key) . '.cache';

    if (file_exists($cacheFile)) {
        return unlink($cacheFile);
    }

    return true;
}

function cache_clear()
{
    $cacheDir = 'cache';

    if (!is_dir($cacheDir)) {
        return true;
    }

    $files = glob($cacheDir . '/*.cache');

    foreach ($files as $file) {
        unlink($file);
    }

    return true;
}

/**
 * URL helper function
 */
function url($path)
{
    return BASE_URL . $path;
}

/**
 * Generate CSRF token for forms
 */
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Search Helper Functions
 */
function searchProducts($query, $limit = 20)
{
    try {
        require_once 'models/Product.php';
        $productModel = new Product();
        return $productModel->search($query, ['name', 'description'], $limit);
    } catch (Exception $e) {
        error_log("Search products error: " . $e->getMessage());
        return [];
    }
}

function searchPets($query, $limit = 20)
{
    try {
        require_once 'models/Pet.php';
        $petModel = new Pet();
        return $petModel->search($query, ['name', 'breed'], $limit);
    } catch (Exception $e) {
        error_log("Search pets error: " . $e->getMessage());
        return [];
    }
}

/**
 * Database search helper
 */
function db_search($table, $query, $columns = [], $limit = 20)
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

    $sql = "SELECT * FROM {$table} WHERE " . implode(' OR ', $searchConditions);

    if ($limit) {
        $sql .= " LIMIT {$limit}";
    }

    return db_select($sql, $params);
}

/**
 * Pagination Helper Functions
 */
function paginate($data, $page = 1, $perPage = 10)
{
    $page = max(1, (int)$page);
    $total = count($data);
    $offset = ($page - 1) * $perPage;

    $paginatedData = array_slice($data, $offset, $perPage);

    return [
        'data' => $paginatedData,
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
 * Product Helper Functions
 */
function getRelatedProducts($productId, $limit = 4)
{
    try {
        $productModel = new Product();
        $product = $productModel->getById($productId);

        if (!$product) {
            return [];
        }

        // Get products of the same type, excluding current product
        $sql = "SELECT * FROM products WHERE type = :type AND id != :id AND is_archived = FALSE ORDER BY RAND() LIMIT :limit";
        return db_select($sql, [
            'type' => $product['type'],
            'id' => $productId,
            'limit' => $limit
        ]);
    } catch (Exception $e) {
        error_log("Get related products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Flash Message Helper Functions
 */
function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][$type][] = $message;
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function hasFlashMessages() {
    return !empty($_SESSION['flash_messages']);
}

function getFlashMessage($type) {
    $messages = $_SESSION['flash_messages'][$type] ?? [];
    unset($_SESSION['flash_messages'][$type]);
    return $messages;
}

function hasFlashMessage($type) {
    return !empty($_SESSION['flash_messages'][$type]);
}
