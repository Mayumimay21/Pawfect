<?php
require_once 'models/Pawket.php';
require_once 'models/Pet.php';
require_once 'models/PetOrder.php';
require_once 'models/User.php';

class PawketController extends Controller {
    private $pawketModel;
    private $petModel;
    private $petOrderModel;
    private $userModel;

    public function __construct() {
        $this->pawketModel = new Pawket();
        $this->petModel = new Pet();
        $this->petOrderModel = new PetOrder();
        $this->userModel = new User();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pawketItems = $this->pawketModel->getPawketItems($_SESSION['user_id']);
        $total = $this->pawketModel->getPawketTotal($_SESSION['user_id']);

        $this->view('pawket/index', [
            'pawketItems' => $pawketItems,
            'total' => $total
        ]);
    }

    public function add() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            return;
        }

        $petId = $_POST['pet_id'] ?? null;
        if (!$petId) {
            echo json_encode(['success' => false, 'message' => 'Invalid pet ID']);
            return;
        }

        $success = $this->pawketModel->addToPawket($_SESSION['user_id'], $petId);
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Pet added to pawket' : 'Failed to add pet to pawket'
        ]);
    }

    public function remove() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $petId = $_POST['pet_id'] ?? null;
        if (!$petId) {
            $_SESSION['error'] = 'Invalid pet ID';
            header('Location: ' . BASE_URL . '/pawket');
            exit;
        }

        $success = $this->pawketModel->removeFromPawket($_SESSION['user_id'], $petId);
        if ($success) {
            $_SESSION['success'] = 'Pet removed from pawket';
        } else {
            $_SESSION['error'] = 'Failed to remove pet from pawket';
        }
        
        header('Location: ' . BASE_URL . '/pawket');
        exit;
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Get all pawket items
        $pawketItems = $this->pawketModel->getPawketItems($_SESSION['user_id']);

        // If no items in pawket, redirect back
        if (empty($pawketItems)) {
            $_SESSION['error'] = 'Your pawket is empty';
            header('Location: ' . BASE_URL . '/pawket');
            exit;
        }

        // Get user data and delivery address
        $user = $this->userModel->getById($_SESSION['user_id']);
        $deliveryAddress = $this->userModel->getPrimaryDeliveryAddress($_SESSION['user_id']);

        // Calculate total for all items
        $total = array_reduce($pawketItems, function($sum, $item) {
            return $sum + $item['price'];
        }, 0);

        $this->view('pawket/checkout', [
            'pawketItems' => $pawketItems,
            'total' => $total,
            'user' => $user,
            'delivery_address' => $deliveryAddress
        ]);
    }

    public function processOrder() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please log in to continue';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Get selected items from POST data
        $selectedItems = $_POST['selected_items'] ?? [];
        
        if (empty($selectedItems)) {
            $_SESSION['error'] = 'Please select pets to checkout';
            header('Location: ' . BASE_URL . '/pawket');
            exit;
        }

        // Get all pawket items
        $pawketItems = $this->pawketModel->getPawketItems($_SESSION['user_id']);

        // Filter items based on selection
        $selectedPets = array_filter($pawketItems, function($item) use ($selectedItems) {
            return in_array($item['id'], $selectedItems);
        });

        if (empty($selectedPets)) {
            $_SESSION['error'] = 'Selected pets not found in your pawket';
            header('Location: ' . BASE_URL . '/pawket');
            exit;
        }

        // Get address details from POST
        $city = $_POST['city'] ?? '';
        $barangay = $_POST['barangay'] ?? '';
        $street = $_POST['street'] ?? '';
        $zipcode = $_POST['zipcode'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'COD';

        // Validate address fields
        if (empty($city) || empty($barangay) || empty($street) || empty($zipcode)) {
            $_SESSION['error'] = 'Please provide a complete delivery address.';
            header('Location: ' . BASE_URL . '/pawket/checkout');
            exit;
        }

        // Find or create the delivery address and get its ID
        $deliveryAddressId = $this->userModel->findOrCreateDeliveryAddress(
            $_SESSION['user_id'],
            $city,
            $barangay,
            $street,
            $zipcode
        );

        if (!$deliveryAddressId) {
            $_SESSION['error'] = 'Failed to save delivery address.';
            header('Location: ' . BASE_URL . '/pawket/checkout');
            exit;
        }

        try {
            // Create adoption order for each selected pet
            foreach ($selectedPets as $pet) {
                try {
                    $orderId = $this->petOrderModel->createOrder(
                        $_SESSION['user_id'],
                        $pet['id'],
                        $pet['price'],
                        $paymentMethod,
                        $deliveryAddressId
                    );

                    // Remove selected pet from pawket
                    $this->pawketModel->removeFromPawket($_SESSION['user_id'], $pet['id']);
                } catch (Exception $e) {
                    error_log("Error creating order for pet ID {$pet['id']}: " . $e->getMessage());
                    throw new Exception("Failed to create adoption order for {$pet['name']}: " . $e->getMessage());
                }
            }

            $_SESSION['success'] = 'Adoption order placed successfully!';
            header('Location: ' . BASE_URL . '/profile');
            exit;
        } catch (Exception $e) {
            error_log("Error in PawketController checkout: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/pawket/checkout');
            exit;
        }
    }
} 