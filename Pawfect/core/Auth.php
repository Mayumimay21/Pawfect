<?php

/**
 * Pawfect Pet Shop - Authentication Helper Functions
 * Handles user authentication and session management
 */

// Start session if not already started
function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in()
{
    start_session();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function user_id()
{
    start_session();
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function current_user()
{
    if (!is_logged_in()) {
        return null;
    }

    static $user = null;

    if ($user === null) {
        $userModel = new User();
        $user = $userModel->find(user_id());

        if ($user) {
            // Remove sensitive data
            unset($user['password']);
        }
    }

    return $user;
}

// Login user
function login_user($userId, $remember = false)
{
    start_session();

    $_SESSION['user_id'] = $userId;
    $_SESSION['login_time'] = time();

    // Update last login
    $userModel = new User();
    $userModel->updateLastLogin($userId);

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        setcookie('remember_token', $token, $expires, '/', '', false, true);

        // Store token in database (you might want to create a remember_tokens table)
        $userModel->update($userId, ['remember_token' => $token]);
    }

    return true;
}

// Logout user
function logout_user()
{
    start_session();

    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);

        // Clear token from database
        if (is_logged_in()) {
            $userModel = new User();
            $userModel->update(user_id(), ['remember_token' => null]);
        }
    }

    // Destroy session
    session_destroy();

    return true;
}

// Check if user has specific role
function has_role($role)
{
    $user = current_user();
    return $user && $user['role'] === $role;
}

// Check if user is admin
function is_admin()
{
    return has_role('admin');
}

// Require authentication
function require_auth($redirectTo = '/login')
{
    if (!is_logged_in()) {
        redirect($redirectTo);
        exit;
    }
}

// Require admin access
function require_admin($redirectTo = '/')
{
    require_auth();

    if (!is_admin()) {
        redirect($redirectTo);
        exit;
    }
}

// Verify CSRF token
function verify_csrf_token($token = null)
{
    start_session();

    if ($token === null) {
        $token = $_POST['_token'] ?? $_GET['_token'] ?? '';
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Attempt to authenticate user with email and password
function attempt_login($email, $password, $remember = false)
{
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        return false;
    }

    if ($user['status'] !== 'active') {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    return login_user($user['id'], $remember);
}

// Check remember me token
function check_remember_token()
{
    if (is_logged_in()) {
        return true;
    }

    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }

    $token = $_COOKIE['remember_token'];
    $userModel = new User();

    $user = db_select_one(
        "SELECT * FROM users WHERE remember_token = :token AND status = 'active'",
        ['token' => $token]
    );

    if ($user) {
        login_user($user['id'], true);
        return true;
    }

    // Invalid token, clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    return false;
}

// Generate password reset token
function generate_password_reset_token($email)
{
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        return false;
    }

    return $userModel->generatePasswordResetToken($user['id']);
}

// Reset password with token
function reset_password_with_token($token, $newPassword)
{
    $userModel = new User();
    $user = $userModel->verifyPasswordResetToken($token);

    if (!$user) {
        return false;
    }

    $result = $userModel->updatePassword($user['id'], $newPassword);

    if ($result) {
        $userModel->clearPasswordResetToken($user['id']);
    }

    return $result;
}

// Check if email is already registered
function email_exists($email, $excludeUserId = null)
{
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        return false;
    }

    if ($excludeUserId && $user['id'] == $excludeUserId) {
        return false;
    }

    return true;
}

// Register new user
function register_user($data)
{
    // Check if email already exists
    if (email_exists($data['email'])) {
        return false;
    }

    $userModel = new User();
    $userId = $userModel->createUser($data);

    if ($userId) {
        // Auto-login after registration
        login_user($userId);
        return $userId;
    }

    return false;
}
