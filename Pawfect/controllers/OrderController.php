<?php
require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/User.php';

class OrderController extends Controller
{
    private $orderModel;
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->productModel = new Product();

        // Ensure user is logged in for all order operations
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please login to access orders.');
            redirect('login');
        }
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getUserOrders($userId, $limit, $offset);
        $totalOrders = $this->orderModel->getUserOrdersCount($userId);
        $totalPages = ceil($totalOrders / $limit);

        $this->view('orders.index', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function show($orderId)
    {
        $userId = $_SESSION['user_id'];
        $order = $this->orderModel->getOrderWithItems($orderId, $userId);

        if (!$order) {
            setFlashMessage('error', 'Order not found.');
            redirect('orders');
        }

        $this->view('orders.show', ['order' => $order]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cart');
        }

        $userId = $_SESSION['user_id'];
        $shippingData = [
            'shipping_address' => $_POST['shipping_address'] ?? '',
            'shipping_city' => $_POST['shipping_city'] ?? '',
            'shipping_barangay' => $_POST['shipping_barangay'] ?? '',
            'shipping_zip' => $_POST['shipping_zip'] ?? '',
            'payment_method' => $_POST['payment_method'] ?? '',
            'notes' => $_POST['notes'] ?? ''
        ];

        // Validate required fields
        $requiredFields = ['shipping_address', 'shipping_city', 'shipping_barangay', 'shipping_zip', 'payment_method'];
        foreach ($requiredFields as $field) {
            if (empty($shippingData[$field])) {
                setFlashMessage('error', "Please provide {$field}.");
                redirect('cart/checkout');
            }
        }

        $orderId = $this->orderModel->createFromCart($userId, $shippingData);

        if (!$orderId) {
            setFlashMessage('error', 'Failed to create order. Please try again.');
            redirect('cart/checkout');
        }

        setFlashMessage('success', 'Order created successfully.');
        redirect('orders/show/' . $orderId);
    }

    public function cancel($orderId)
    {
        $userId = $_SESSION['user_id'];

        if (!$this->orderModel->canBeCancelled($orderId, $userId)) {
            setFlashMessage('error', 'This order cannot be cancelled.');
            redirect('orders');
        }

        $reason = $_POST['reason'] ?? null;
        $success = $this->orderModel->cancelOrder($orderId, $userId, $reason);

        if ($success) {
            setFlashMessage('success', 'Order cancelled successfully.');
        } else {
            setFlashMessage('error', 'Failed to cancel order. Please try again.');
        }

        redirect('orders');
    }

    public function updateStatus($orderId)
    {
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to update order status.');
            redirect('orders');
        }

        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? null;

        if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            setFlashMessage('error', 'Invalid order status.');
            redirect('orders/show/' . $orderId);
        }

        $success = $this->orderModel->updateStatus($orderId, $status, $notes);

        if ($success) {
            setFlashMessage('success', 'Order status updated successfully.');
        } else {
            setFlashMessage('error', 'Failed to update order status. Please try again.');
        }

        redirect('orders/show/' . $orderId);
    }

    public function adminIndex()
    {
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to access this page.');
            redirect('orders');
        }

        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get filter parameters
        $query = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $orders = $this->orderModel->getAdminPaginated($limit, $offset, $query, $status, $startDate, $endDate);
        $totalOrders = $this->orderModel->getAdminTotalCount($query, $status, $startDate, $endDate);
        $totalPages = ceil($totalOrders / $limit);

        $this->view('admin/orders/index', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'search' => $query,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    public function statistics()
    {
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to access this page.');
            redirect('orders');
        }

        $stats = $this->orderModel->getStats();
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $salesData = $this->orderModel->getSalesData();
        $topCustomers = $this->orderModel->getTopCustomers(5);

        $this->view('admin/orders/statistics', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'salesData' => $salesData,
            'topCustomers' => $topCustomers
        ]);
    }
}
