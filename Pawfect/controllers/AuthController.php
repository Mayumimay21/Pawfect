<?php
require_once 'models/User.php';

class AuthController extends Controller {
    public function login() {
        if (isLoggedIn()) {
            $this->redirect('/');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $userModel = new User();
            $user = $userModel->getByEmail($email);
            
            if ($user && !$user['is_banned'] && $userModel->verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                if ($user['role'] === 'admin') {
                    $this->redirect('/admin');
                } else {
                    $this->redirect('/');
                }
            } else {
                $_SESSION['error'] = 'Invalid credentials or account banned';
            }
        }
        
        $this->view('auth/login');
    }
    
    public function register() {
        if (isLoggedIn()) {
            $this->redirect('/');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'phone' => $_POST['phone'],
            ];
            
            $userModel = new User();
            
            // Check if email exists
            if ($userModel->getByEmail($data['email'])) {
                $_SESSION['error'] = 'Email already exists';
            } else {
                if ($userModel->create($data)) {
                    $_SESSION['success'] = 'Registration successful! Please login.';
                    $this->redirect('/login');
                } else {
                    $_SESSION['error'] = 'Registration failed';
                }
            }
        }
        
        $this->view('auth/register');
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
?>
