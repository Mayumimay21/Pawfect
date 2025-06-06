<?php
require_once 'models/User.php';
require_once 'models/Pet.php';
require_once 'models/Product.php';
require_once 'models/Order.php';
require_once 'models/PetOrder.php';
require_once 'models/Category.php';

class AdminController extends Controller {
    public function __construct() {
        if (!isAdmin()) {
            $this->redirect('/');
            exit;
        }
    }
    
    public function dashboard() {
        $petModel = new Pet();
        $productModel = new Product();
        $orderModel = new Order();
        $userModel = new User();
        
        $petStats = $petModel->getStats();
        $productStats = $productModel->getStats();
        $orderStats = $orderModel->getStats();
        $users = $userModel->getAll();
        $topSoldProducts = $productModel->getTopSoldProducts();
        $topOutOfStockProducts = $productModel->getTopOutOfStockProducts();
        
        $this->view('admin/dashboard', [
            'petStats' => $petStats,
            'productStats' => $productStats,
            'orderStats' => $orderStats,
            'users' => $users,
            'topSoldProducts' => $topSoldProducts,
            'topOutOfStockProducts' => $topOutOfStockProducts
        ]);
    }
    
    public function pets() {
        $petModel = new Pet();
        
        // Pagination settings for admin
        $limit = 10; // Number of rows per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get search and filter parameters for admin pets
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? null;
        $gender = $_GET['gender'] ?? null;
        $breed = $_GET['breed'] ?? null;
        $minAge = isset($_GET['min_age']) && $_GET['min_age'] !== '' ? (int)$_GET['min_age'] : null;
        $maxAge = isset($_GET['max_age']) && $_GET['max_age'] !== '' ? (int)$_GET['max_age'] : null;

        error_log("AdminController pets - Parameters: query=$query, type=$type, gender=$gender, breed=$breed, minAge=$minAge, maxAge=$maxAge");

        // Get paginated pets and total count based on search and filters
        $pets = $petModel->getAdminPaginated($limit, $offset, $query, $type, $gender, $breed, $minAge, $maxAge);
        $totalPets = $petModel->getAdminTotalCount($query, $type, $gender, $breed, $minAge, $maxAge);

        error_log("AdminController pets - Total pets: $totalPets");

        $totalPages = ceil($totalPets / $limit);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            
            if ($action === 'create') {
                $imagePath = null;
                if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/pets/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['pet_image']['name']));
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $targetFile)) {
                        $imagePath = '/uploads/pets/' . $filename;
                    }
                }
                $data = [
                    'name' => $_POST['name'],
                    'pet_image' => $imagePath,
                    'type' => $_POST['type'],
                    'gender' => $_POST['gender'],
                    'age' => $_POST['age'],
                    'breed' => $_POST['breed'],
                    'description' => $_POST['description']
                ];
                $petModel->create($data);
            } elseif ($action === 'update') {
                $imagePath = $_POST['current_pet_image'];
                if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/pets/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['pet_image']['name']));
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $targetFile)) {
                        $imagePath = '/uploads/pets/' . $filename;
                        // Delete old image if it exists and is not the default image
                        if ($_POST['current_pet_image'] && $_POST['current_pet_image'] !== '/uploads/pets/default.jpg') {
                            $oldImagePath = __DIR__ . '/..' . $_POST['current_pet_image'];
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    }
                }
                $data = [
                    'name' => $_POST['name'],
                    'pet_image' => $imagePath,
                    'type' => $_POST['type'],
                    'gender' => $_POST['gender'],
                    'age' => $_POST['age'],
                    'breed' => $_POST['breed'],
                    'description' => $_POST['description']
                ];
                $petModel->update($_POST['id'], $data);
            } elseif ($action === 'delete') {
                $petModel->delete($_POST['id']);
            }
            
            // Redirect to the current page after action
            $this->redirect('/admin/pets?page=' . $page);
        }
        
        $this->view('admin/pets', [
            'pets' => $pets,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $query,
            'filterType' => $type,
            'filterGender' => $gender,
            'filterBreed' => $breed,
            'filterMinAge' => $minAge,
            'filterMaxAge' => $maxAge,
            'pageTitle' => 'Manage Pets'
        ]);
    }
    
    public function products() {
        $productModel = new Product();
        $categoryModel = new Category();
        
        // Pagination settings for admin
        $limit = 10; // Number of rows per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Get search and filter parameters for admin products
        $query = $_GET['q'] ?? '';
        $isArchived = $_GET['is_archived'] ?? null;

        // Get paginated products and total count based on search and filters
        $products = $productModel->getAdminPaginated($limit, $offset, $query, $isArchived);
        $totalProducts = $productModel->getAdminTotalCount($query, $isArchived);
        $categories = $categoryModel->getAll();

        $totalPages = ceil($totalProducts / $limit);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            
            if ($action === 'create') {
                $imagePath = null;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['product_image']['name']));
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
                        $imagePath = '/uploads/products/' . $filename;
                    }
                }
                $data = [
                    'name' => $_POST['name'],
                    'product_image' => $imagePath,
                    'stock_quantity' => $_POST['stock_quantity'],
                    'type' => $_POST['type'],
                    'price' => $_POST['price'],
                    'description' => $_POST['description'],
                    'category_id' => $_POST['category_id']
                ];
                $productModel->create($data);
            } elseif ($action === 'update') {
                $imagePath = $_POST['current_product_image'] ?? null;
                
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['product_image']['name']));
                    $targetFile = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
                        $imagePath = '/uploads/products/' . $filename;
                        
                        if (!empty($_POST['current_product_image']) && 
                            $_POST['current_product_image'] !== '/assets/images/default-product.png' && 
                            file_exists(__DIR__ . '/..' . $_POST['current_product_image'])) {
                            unlink(__DIR__ . '/..' . $_POST['current_product_image']);
                        }
                    }
                }
                
                $data = [
                    'name' => $_POST['name'],
                    'product_image' => $imagePath,
                    'stock_quantity' => $_POST['stock_quantity'],
                    'type' => $_POST['type'],
                    'price' => $_POST['price'],
                    'description' => $_POST['description'],
                    'category_id' => $_POST['category_id']
                ];
                $productModel->update($_POST['id'], $data);
            } elseif ($action === 'archive') {
                $productModel->archive($_POST['id']);
            } elseif ($action === 'restore') {
                $productModel->restore($_POST['id']);
            }
            
            $redirectUrl = '/admin/pawducts?page=' . $page;
            if ($isArchived) {
                $redirectUrl .= '&is_archived=1';
            }
            $this->redirect($redirectUrl);
        }
        
        $this->view('admin/products', [
            'products' => $products,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $query,
            'isArchivedFilter' => $isArchived,
            'pageTitle' => 'Manage Pawducts'
        ]);
    }
    
    public function orders() {
        $orderModel = new Order();
        
        // Pagination settings for admin
        $limit = 10; // Number of rows per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Get search and filter parameters for admin orders
        $query = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        // Get paginated orders and total count for admin view
        // This assumes getAdminPaginated and getAdminTotalCount in Order.php
        // are updated to handle search query, status, and date range filters.
        $orders = $orderModel->getAdminPaginated($limit, $offset, $query, $status, $startDate, $endDate); // Add new parameters
        $totalOrders = $orderModel->getAdminTotalCount($query, $status, $startDate, $endDate); // Add new parameters

        $totalPages = ceil($totalOrders / $limit);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'];
            $newStatus = $_POST['status'];
            $order = $orderModel->getById($orderId);
            if ($order['status'] === 'delivered' && in_array($newStatus, ['shipped', 'delivered'])) {
                $_SESSION['error'] = 'Cannot change status. Order is already delivered.';
            } else {
                $orderModel->updateStatus($orderId, $newStatus);
                $_SESSION['success'] = 'Order status updated!';
            }
             // Redirect to the current page after action
             $this->redirect('/admin/orders?page=' . $page);
        }
        
        $this->view('admin/orders', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $query, // Pass search query to view
            'filterStatus' => $status, // Pass status filter to view
            'filterStartDate' => $startDate, // Pass start date filter to view
            'filterEndDate' => $endDate, // Pass end date filter to view
            'pageTitle' => 'Manage Orders'
        ]);
    }
    
    public function users() {
        $userModel = new User();
        
        // Pagination settings for admin
        $limit = 10; // Number of rows per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Get search and filter parameters for admin users
        $query = $_GET['q'] ?? '';
        $role = $_GET['role'] ?? null;

        // Get paginated users and total count for admin view
        // This assumes getAdminPaginated and getAdminTotalCount in User.php
        // are updated to handle search query and role filter.
        $users = $userModel->getAdminPaginated($limit, $offset, $query, $role); // Add new parameters
        $totalUsers = $userModel->getAdminTotalCount($query, $role); // Add new parameters

        $totalPages = ceil($totalUsers / $limit);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            $userId = $_POST['user_id'];
            
            if ($action === 'ban') {
                $userModel->banUser($userId);
            } elseif ($action === 'unban') {
                $userModel->unbanUser($userId);
            } elseif ($action === 'role') {
                $userModel->updateRole($userId, $_POST['role']);
            }
            
            // Redirect to the current page after action
            $this->redirect('/admin/users?page=' . $page);
        }
        
        $this->view('admin/users', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $query, // Pass search query to view
            'filterRole' => $role, // Pass role filter to view
            'pageTitle' => 'Manage Users'
        ]);
    }
    
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle logo upload
            $logoPath = getSetting('site_logo'); // Keep existing logo by default
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/uploads/logo/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                // Generate unique filename
                $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['site_logo']['name']));
                $targetFile = $uploadDir . $filename;
                
                // Check if image file is a actual image
                $check = getimagesize($_FILES['site_logo']['tmp_name']);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $targetFile)) {
                        $logoPath = BASE_URL . '/public/uploads/logo/' . $filename;
                    }
                }
            }
            
            // Update settings
            setSetting('site_logo', $logoPath);
            setSetting('primary_color', $_POST['primary_color']);
            setSetting('secondary_color', $_POST['secondary_color']);
            
            $_SESSION['success'] = 'Settings updated successfully!';
            $this->redirect('/admin/settings');
        }
        
        $this->view('admin/settings', [
            'pageTitle' => 'Settings'
        ]);
    }

    public function adoptionOrders() {
        $petOrderModel = new PetOrder();
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $query = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? null;

        // Get paginated pet orders and total count
        $orders = $petOrderModel->getAdminPaginated($limit, $offset, $query, $status);
        $totalOrders = $petOrderModel->getAdminTotalCount($query, $status);
        $totalPages = ceil($totalOrders / $limit);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'];
            $newStatus = $_POST['status'];
            $petOrderModel->updateStatus($orderId, $newStatus);
            $_SESSION['success'] = 'Pet order status updated!';
            $this->redirect('/admin/pet-orders?page=' . $page);
        }

        $this->view('admin/pet_orders', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $query,
            'filterStatus' => $status,
            'pageTitle' => 'Manage Pet Orders'
        ]);
    }

    public function updatePetOrderStatus() {
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to update pet order status.');
            $this->redirect('/admin/pet-orders');
        }

        $orderId = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? null;

        if (!$orderId || !in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
            setFlashMessage('error', 'Invalid request parameters.');
            $this->redirect('/admin/pet-orders');
        }

        $petOrderModel = new PetOrder();
        $petModel = new Pet();

        try {
            // Update the order status
            $success = $petOrderModel->updateStatus($orderId, $status, $adminNotes);
            setFlashMessage('success', 'Pet order status updated successfully.');
        } catch (Exception $e) {
            error_log("Error in updatePetOrderStatus: " . $e->getMessage());
            setFlashMessage('error', 'Failed to update pet order status: ' . $e->getMessage());
        }

        $this->redirect('/admin/pet-orders');
    }

    public function updateOrderStatus() {
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to update order status.');
            $this->redirect('/admin/orders');
        }

        $orderId = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? null;

        if (!$orderId || !in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            setFlashMessage('error', 'Invalid request parameters.');
            $this->redirect('/admin/orders');
        }

        $orderModel = new Order();

        try {
            // Update the order status
            $success = $orderModel->updateStatus($orderId, $status, $adminNotes);
            setFlashMessage('success', 'Order status updated successfully.');
        } catch (Exception $e) {
            error_log("Error in updateOrderStatus: " . $e->getMessage());
            setFlashMessage('error', 'Failed to update order status: ' . $e->getMessage());
        }

        $this->redirect('/admin/orders');
    }
}
?>
