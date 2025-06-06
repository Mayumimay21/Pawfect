<?php
require_once 'models/User.php';
require_once 'models/Order.php';
require_once 'models/Pet.php';

class UserController extends Controller
{
    public function profile()
    {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userModel = new User();
        $orderModel = new Order();
        $petModel = new Pet();

        // Handle cancel order action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
            $orderId = $_POST['order_id'] ?? null;

            if (!$orderId) {
                $_SESSION['error'] = 'Invalid cancellation request';
            } else {
                // Check if the order belongs to the user and can be cancelled
                if (!$orderModel->canBeCancelled($orderId, $_SESSION['user_id'])) {
                    $_SESSION['error'] = 'This order cannot be cancelled';
                } else {
                    // Attempt to cancel the order
                    if ($orderModel->cancelOrder($orderId, $_SESSION['user_id'])) {
                        $_SESSION['success'] = 'Order cancelled successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to cancel order';
                    }
                }
            }
            $this->redirect('/profile');
            return;
        }

        $user = $userModel->getById($_SESSION['user_id']);

        // Pagination settings for active orders
        $limit = 5; // Number of orders per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Get paginated active orders for the user
        $orders = $orderModel->getUserOrders($_SESSION['user_id'], $limit, $offset, ['pending', 'processing', 'shipped', 'delivered']);
        $totalOrders = $orderModel->getUserOrdersCount($_SESSION['user_id'], ['pending', 'processing', 'shipped', 'delivered']);
        $totalPages = ceil($totalOrders / $limit);

        // Debug: Log orders data
        error_log("User ID: " . $_SESSION['user_id']);
        error_log("Total Orders: " . $totalOrders);
        error_log("Orders Data: " . print_r($orders, true));

        // Pagination settings for cancelled orders
        $cancelledPage = isset($_GET['cancelled_page']) ? (int)$_GET['cancelled_page'] : 1;
        $cancelledOffset = ($cancelledPage - 1) * $limit;

        // Get paginated cancelled orders for the user
        $cancelledOrders = $orderModel->getUserOrders($_SESSION['user_id'], $limit, $cancelledOffset, ['cancelled']);
        $totalCancelledOrders = $orderModel->getUserOrdersCount($_SESSION['user_id'], ['cancelled']);
        $cancelledTotalPages = ceil($totalCancelledOrders / $limit);

        // Debug: Log cancelled orders data
        error_log("Total Cancelled Orders: " . $totalCancelledOrders);
        error_log("Cancelled Orders Data: " . print_r($cancelledOrders, true));

        // Fetch user's delivery addresses
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM delivery_addresses WHERE user_id = ? ORDER BY id LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $deliveryAddress = $stmt->fetch() ?: []; // Fetch one address, default to empty array if none found

        // Get adopted pets
        $adoptedPets = $petModel->getAdoptedPetsByUser($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];

            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', basename($_FILES['avatar']['name']));
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    $data['avatar'] = '/uploads/avatars/' . $filename;
                }
            }

            if ($userModel->update($_SESSION['user_id'], $data)) {
                $_SESSION['success'] = 'Profile updated successfully!';
                $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
            $this->redirect('/profile');
        }

        $this->view('user/profile', [
            'user' => $user,
            'orders' => $orders,
            'cancelledOrders' => $cancelledOrders,
            'delivery_address' => $deliveryAddress,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'cancelledCurrentPage' => $cancelledPage,
            'cancelledTotalPages' => $cancelledTotalPages,
            'adoptedPets' => $adoptedPets,
            'pageTitle' => 'Profile'
        ]);
    }

    public function updateAddress()
    {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        $userId = $_SESSION['user_id'];
        $city = $_POST['city'] ?? '';
        $barangay = $_POST['barangay'] ?? '';
        $street = $_POST['street'] ?? '';
        $zipcode = $_POST['zipcode'] ?? '';
        if ($city && $barangay && $street && $zipcode) {
            global $pdo;
            // Check if address exists
            $stmt = $pdo->prepare('SELECT id FROM delivery_addresses WHERE user_id = ?');
            $stmt->execute([$userId]);
            if ($stmt->fetch()) {
                // Update
                $stmt = $pdo->prepare('UPDATE delivery_addresses SET city=?, barangay=?, street=?, zipcode=? WHERE user_id=?');
                $stmt->execute([$city, $barangay, $street, $zipcode, $userId]);
            } else {
                // Insert
                $stmt = $pdo->prepare('INSERT INTO delivery_addresses (user_id, city, barangay, street, zipcode) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $city, $barangay, $street, $zipcode]);
            }
        }
        $this->redirect('/profile');
    }
}
