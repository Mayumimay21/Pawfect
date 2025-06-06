<?php
/**
 * Pawfect Pet Shop - Cart Controller
 * Handles shopping cart functionality
 */

require_once 'models/Cart.php';
require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/User.php';

class CartController extends Controller {
    
    public function index() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $cartModel = new Cart();
        $items = $cartModel->getItems($_SESSION['user_id']);
        $total = $cartModel->getTotal($_SESSION['user_id']);
        
        $this->view('cart/index', [
            'items' => $items,
            'total' => $total
        ]);
    }
    
    public function add() {
        if (!isLoggedIn()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Please login first']);
                return;
            }
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'];
            $quantity = $_POST['quantity'] ?? 1;
            
            $cartModel = new Cart();
            if ($cartModel->addItem($_SESSION['user_id'], $productId, $quantity)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Item added to cart']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to add item']);
            }
        }
    }
    
    public function update() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            
            $cartModel = new Cart();
            $cartModel->updateQuantity($_SESSION['user_id'], $productId, $quantity);
            $this->redirect('/cart');
        }
    }
    
    public function remove() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'];
            
            $cartModel = new Cart();
            $cartModel->removeItem($_SESSION['user_id'], $productId);
            $this->redirect('/cart');
        }
    }
    
    public function checkout() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $cartModel = new Cart();
        $items = $cartModel->getItems($_SESSION['user_id']);

        // Filter items based on selection if provided
        if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
            $selectedIds = array_map('intval', $_POST['selected_items']);
            $items = array_filter($items, function($item) use ($selectedIds) {
                return in_array($item['product_id'], $selectedIds);
            });
            // Store selected items in session
            $_SESSION['checkout_items'] = $selectedIds;
        } else {
            // If no items selected, redirect back to cart
            $_SESSION['error'] = 'Please select items to checkout';
            $this->redirect('/cart');
            return;
        }

        if (empty($items)) {
            $_SESSION['error'] = 'Please select items to checkout';
            $this->redirect('/cart');
            return;
        }

        // Calculate total for selected items
        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        // Show confirmation page for address/payment
        $userModel = new User();
        $user = $userModel->getById($_SESSION['user_id']);
        
        // Fetch user's primary delivery address
        $deliveryAddress = $userModel->getPrimaryDeliveryAddress($_SESSION['user_id']);

        $this->view('cart/checkout', [
            'items' => $items,
            'total' => $total,
            'user' => $user,
            'delivery_address' => $deliveryAddress
        ]);
    }

    public function confirmCheckout() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $cartModel = new Cart();
        $orderModel = new Order();
        $productModel = new Product();
        $userModel = new User();

        // Get items from cart
        $items = $cartModel->getItems($_SESSION['user_id']);

        // Filter items based on session stored selection
        if (isset($_SESSION['checkout_items']) && is_array($_SESSION['checkout_items'])) {
            $selectedIds = $_SESSION['checkout_items'];
            $items = array_filter($items, function($item) use ($selectedIds) {
                return in_array($item['product_id'], $selectedIds);
            });
        } else {
            $_SESSION['error'] = 'No items selected for checkout';
            $this->redirect('/cart');
            return;
        }

        if (empty($items)) {
            $_SESSION['error'] = 'Please select items to checkout';
            $this->redirect('/cart');
            return;
        }

        // Calculate total for selected items
        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        // Get address details from POST
        $city = $_POST['city'] ?? '';
        $barangay = $_POST['barangay'] ?? '';
        $street = $_POST['street'] ?? '';
        $zipcode = $_POST['zipcode'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';

        // Validate address fields (basic check)
        if (empty($city) || empty($barangay) || empty($street) || empty($zipcode)) {
            $_SESSION['error'] = 'Please provide a complete delivery address.';
            $this->redirect('/cart/checkout');
            return;
        }

        // Find or create the delivery address and get its ID
        $deliveryAddressId = $userModel->findOrCreateDeliveryAddress($_SESSION['user_id'], $city, $barangay, $street, $zipcode);

        if (!$deliveryAddressId) {
            $_SESSION['error'] = 'Failed to save delivery address.';
            $this->redirect('/cart/checkout');
            return;
        }

        // Create order with delivery_address_id
        $orderId = $orderModel->createOrder($_SESSION['user_id'], $total, $deliveryAddressId, $paymentMethod);

        if ($orderId) {
            foreach ($items as $item) {
                // Add selected items to order
                $orderModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
                $productModel->updateStock($item['product_id'], $item['quantity']);
                // Remove selected items from cart
                $cartModel->removeItem($_SESSION['user_id'], $item['product_id']);
            }

            // Clear the checkout items from session
            unset($_SESSION['checkout_items']);

            $_SESSION['success'] = 'Order placed successfully!';
            $this->redirect('/profile'); // Redirect to profile or order details page
        } else {
            $_SESSION['error'] = 'Failed to place order.';
            $this->redirect('/cart/checkout');
        }
    }
    
    public function count() {
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }
        
        $cartModel = new Cart();
        $count = $cartModel->getItemCount($_SESSION['user_id']);
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
}
?>
