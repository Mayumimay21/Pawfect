<?php
require_once 'models/PetOrder.php';
require_once 'models/Pet.php';
require_once 'models/User.php';

class PetOrderController extends Controller
{
    private $petOrderModel;
    private $petModel;

    public function __construct()
    {
        $this->petOrderModel = new PetOrder();
        $this->petModel = new Pet();
        
        // Ensure user is logged in for all order operations
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please login to access pet orders.');
            redirect('login');
        }
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $orders = $this->petOrderModel->getUserOrders($userId, $limit, $offset);
        $totalOrders = $this->petOrderModel->getUserOrdersCount($userId);
        $totalPages = ceil($totalOrders / $limit);

        $this->view('pet_orders/index', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function show($orderId)
    {
        $userId = $_SESSION['user_id'];
        $order = $this->petOrderModel->getOrderWithPets($orderId, $userId);

        if (!$order) {
            setFlashMessage('error', 'Pet order not found.');
            redirect('pet-orders');
        }

        $this->view('pet_orders/show', ['order' => $order]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pets');
        }

        $userId = $_SESSION['user_id'];
        $petId = $_POST['pet_id'] ?? null;
        $totalAmount = $_POST['total_amount'] ?? 0;

        if (!$petId) {
            setFlashMessage('error', 'Please select a pet to adopt.');
            redirect('pets');
        }

        $orderId = $this->petOrderModel->createOrder($userId, $petId, $totalAmount);

        if (!$orderId) {
            setFlashMessage('error', 'Failed to create pet order. Please try again.');
            redirect('pets');
        }

        setFlashMessage('success', 'Pet order created successfully.');
        redirect('pet-orders/show/' . $orderId);
    }

    public function cancel($orderId)
    {
        $userId = $_SESSION['user_id'];

        if (!$this->petOrderModel->canBeCancelled($orderId, $userId)) {
            setFlashMessage('error', 'This pet order cannot be cancelled.');
            redirect('pet-orders');
        }

        $reason = $_POST['reason'] ?? null;
        $success = $this->petOrderModel->cancelOrder($orderId, $userId, $reason);

        if ($success) {
            setFlashMessage('success', 'Pet order cancelled successfully.');
        } else {
            setFlashMessage('error', 'Failed to cancel pet order. Please try again.');
        }

        redirect('pet-orders');
    }

    public function updateStatus($orderId)
    {
        if (!isAdmin()) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'You do not have permission to update pet order status.']);
                return;
            }
            setFlashMessage('error', 'You do not have permission to update pet order status.');
            redirect('pet-orders');
        }

        $status = $_POST['status'] ?? '';
        $notes = $_POST['admin_notes'] ?? null;

        if (!in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Invalid pet order status.']);
                return;
            }
            setFlashMessage('error', 'Invalid pet order status.');
            redirect('admin/pet-orders');
        }

        // Start transaction
        global $pdo;
        $pdo->beginTransaction();

        try {
            // Get the order details first
            $order = $this->petOrderModel->getOrderWithPets($orderId);
            
            if (!$order) {
                throw new Exception('Order not found');
            }

            // Update the order status
            $success = $this->petOrderModel->updateStatus($orderId, $status, $notes);

            if (!$success) {
                throw new Exception('Failed to update order status');
            }

            // If status is approved, update the pet's status to adopted
            if ($status === 'approved') {
                $petId = $order['pet_id'];
                $userId = $order['user_id'];
                
                // Update pet status to adopted and set adopted_by
                $petUpdateSuccess = $this->petModel->updatePetStatus($petId, 'adopted', $userId);
                
                if (!$petUpdateSuccess) {
                    throw new Exception('Failed to update pet status');
                }
            }
            // If status is cancelled or rejected, update the pet's status back to available
            else if ($status === 'cancelled' || $status === 'rejected') {
                $petId = $order['pet_id'];
                $petUpdateSuccess = $this->petModel->updatePetStatus($petId, 'available', null);
                
                if (!$petUpdateSuccess) {
                    throw new Exception('Failed to update pet status');
                }
            }

            // Commit transaction
            $pdo->commit();
            
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => true, 'message' => 'Pet order status updated successfully.']);
                return;
            }
            
            setFlashMessage('success', 'Pet order status updated successfully.');
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update pet order status: ' . $e->getMessage()]);
                return;
            }
            
            setFlashMessage('error', 'Failed to update pet order status: ' . $e->getMessage());
        }

        if (!$this->isAjaxRequest()) {
            redirect('admin/pet-orders');
        }
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
?> 