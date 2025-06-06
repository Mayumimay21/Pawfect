<?php
require_once 'config/config.php';

// Include required files
require_once 'core/Controller.php';
require_once 'core/Model.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';
require_once 'core/Helpers.php';

// Simple routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/Pawfect', '', $path);

// Remove trailing slash
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

switch ($path) {
    case '/':
    case '/home':
        require_once 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
    
    case '/pets':
        require_once 'controllers/PetController.php';
        $controller = new PetController();
        $controller->index();
        break;
    
    case '/pawducts':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;
    
    case '/cart':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->index();
        break;
    
    case '/cart/add':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->add();
        break;
    
    case '/cart/update':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->update();
        break;
    
    case '/cart/remove':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->remove();
        break;
    
    case '/cart/checkout':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->checkout();
        break;
    
    case '/cart/count':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->count();
        break;
    
    case '/cart/confirm-checkout':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        $controller->confirmCheckout();
        break;
    
    case '/login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
    
    case '/register':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;
    
    case '/logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
    
    case '/profile':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->profile();
        break;
    
    case '/profile/update-address':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->updateAddress();
        break;
    
    case '/adopted-pets':
        require_once 'controllers/PetController.php';
        $controller = new PetController();
        $controller->adoptedPets();
        break;
    
    case '/pets/adopt':
        require_once 'controllers/PetController.php';
        $controller = new PetController();
        $controller->adopt();
        break;
    
    case '/admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;
    
    case '/admin/pets':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->pets();
        break;
    
    case '/admin/pawducts':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->products();
        break;
    
    case '/admin/orders':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->orders();
        break;
    
    case '/admin/users':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
        break;
    
    case '/admin/settings':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settings();
        break;
    
    case '/user/pet-orders':
        require_once 'controllers/PetOrderController.php';
        $controller = new PetOrderController();
        $controller->index();
        break;
    
    case '/pet-orders':
        require_once 'controllers/PetOrderController.php';
        $controller = new PetOrderController();
        $controller->index();
        break;
    
    case '/pet-orders/show':
        require_once 'controllers/PetOrderController.php';
        $controller = new PetOrderController();
        $controller->show($_GET['id']);
        break;
    
    case '/pet-orders/cancel':
        require_once 'controllers/PetOrderController.php';
        $controller = new PetOrderController();
        $controller->cancel($_POST['order_id']);
        break;
    
    case '/pet-orders/update-status':
        require_once 'controllers/PetOrderController.php';
        $controller = new PetOrderController();
        $controller->updateStatus($_POST['order_id']);
        break;
    
    case '/admin/pet-orders':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->adoptionOrders();
        break;
    
    case '/admin/pet-orders/update-status':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updatePetOrderStatus();
        break;
    
    case '/admin/orders/update-status':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateOrderStatus();
        break;
    
    case '/pawket':
        require_once 'controllers/PawketController.php';
        $controller = new PawketController();
        $controller->index();
        break;
    
    case '/pawket/add':
        require_once 'controllers/PawketController.php';
        $controller = new PawketController();
        $controller->add();
        break;
    
    case '/pawket/remove':
        require_once 'controllers/PawketController.php';
        $controller = new PawketController();
        $controller->remove();
        break;
    
    case '/pawket/checkout':
        require_once 'controllers/PawketController.php';
        $controller = new PawketController();
        $controller->checkout();
        break;
    
    case '/pawket/process-order':
        require_once 'controllers/PawketController.php';
        $controller = new PawketController();
        $controller->processOrder();
        break;
    
    default:
        // Handle dynamic routes
        if (preg_match('/^\/pet\/(\d+)$/', $path, $matches)) {
            require_once 'controllers/PetController.php';
            $controller = new PetController();
            $controller->show($matches[1]);
        } elseif (preg_match('/^\/product\/(\d+)$/', $path, $matches)) {
            require_once 'controllers/ProductController.php';
            $controller = new ProductController();
            $controller->show($matches[1]);
        } else {
            http_response_code(404);
            require_once 'views/404.php';
        }
        break;
}

?>
