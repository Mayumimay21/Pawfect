<?php
require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/User.php';

class OrderController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Ensure user is logged in for all order operations
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please login to access orders.');
            redirect('login');
        }
    }

    public function index()
    {
        $orders = Order::with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('orders.index', compact('orders'));
    }

    public function show($orderId)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to view this order.');
            redirect('orders');
        }
        
        return view('orders.show', compact('order'));
    }

    public function edit($orderId)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to edit this order.');
            redirect('orders');
        }
        
        if ($order->status !== 'pending') {
            setFlashMessage('error', 'Only pending orders can be edited.');
            redirect('orders');
        }
        
        return view('orders.edit', compact('order'));
    }

    public function update($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to update this order.');
            redirect('orders');
        }
        
        if ($order->status !== 'pending') {
            setFlashMessage('error', 'Only pending orders can be updated.');
            redirect('orders');
        }

        $items = $_POST['items'] ?? [];
        if (empty($items)) {
            setFlashMessage('error', 'No items provided.');
            redirect('orders/edit/' . $orderId);
        }

        try {
            DB::beginTransaction();

            // Delete existing items
            $order->items()->delete();

            // Add new items
            $total = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }
                
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);
            }

            $order->update([
                'total_amount' => $total,
                'updated_at' => now()
            ]);

            DB::commit();
            setFlashMessage('success', 'Order updated successfully.');
            redirect('orders/show/' . $orderId);
        } catch (Exception $e) {
            DB::rollBack();
            setFlashMessage('error', $e->getMessage() ?: 'Failed to update order. Please try again.');
            redirect('orders/edit/' . $orderId);
        }
    }

    public function destroy($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to cancel this order.');
            redirect('orders');
        }
        
        if ($order->status !== 'pending') {
            setFlashMessage('error', 'Only pending orders can be cancelled.');
            redirect('orders');
        }

        try {
            DB::beginTransaction();
            $order->items()->delete();
            $order->delete();
            DB::commit();
            setFlashMessage('success', 'Order cancelled successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            setFlashMessage('error', 'Failed to cancel order. Please try again.');
        }
        
        redirect('orders');
    }

    public function confirm($orderId)
    {
        // Only admin can confirm orders
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to confirm orders.');
            redirect('orders');
        }

        $order = Order::findOrFail($orderId);
        
        if ($order->status !== 'pending') {
            setFlashMessage('error', 'Only pending orders can be confirmed.');
            redirect('orders');
        }

        try {
            DB::beginTransaction();
            $order->update([
                'status' => 'confirmed',
                'updated_at' => now()
            ]);
            DB::commit();
            setFlashMessage('success', 'Order confirmed successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            setFlashMessage('error', 'Failed to confirm order. Please try again.');
        }
        
        redirect('orders/show/' . $orderId);
    }

    public function complete($orderId)
    {
        // Only admin can complete orders
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to complete orders.');
            redirect('orders');
        }

        $order = Order::findOrFail($orderId);
        
        if ($order->status !== 'confirmed') {
            setFlashMessage('error', 'Only confirmed orders can be completed.');
            redirect('orders');
        }

        try {
            DB::beginTransaction();
            $order->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);
            DB::commit();
            setFlashMessage('success', 'Order marked as completed.');
        } catch (Exception $e) {
            DB::rollBack();
            setFlashMessage('error', 'Failed to complete order. Please try again.');
        }
        
        redirect('orders/show/' . $orderId);
    }

    public function cancel($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to cancel this order.');
            redirect('orders');
        }
        
        if ($order->status === 'completed') {
            setFlashMessage('error', 'Completed orders cannot be cancelled.');
            redirect('orders');
        }

        try {
            DB::beginTransaction();
            $order->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);
            DB::commit();
            setFlashMessage('success', 'Order cancelled successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            setFlashMessage('error', 'Failed to cancel order. Please try again.');
        }
        
        redirect('orders/show/' . $orderId);
    }

    public function export()
    {
        // Only admin can export orders
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to export orders.');
            redirect('orders');
        }

        $orders = Order::with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders.csv"');

        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, [
            'Order ID',
            'Customer',
            'Date',
            'Status',
            'Total Amount',
            'Items',
            'Payment Method'
        ]);

        // Add data
        foreach ($orders as $order) {
            $items = array_map(function($item) {
                return $item['product']['name'] . ' (x' . $item['quantity'] . ')';
            }, $order->items);

            fputcsv($output, [
                $order->id,
                $order->user->name,
                date('Y-m-d H:i:s', strtotime($order->created_at)),
                ucfirst($order->status),
                number_format($order->total_amount, 2),
                implode(', ', $items),
                ucfirst($order->payment_method)
            ]);
        }

        fclose($output);
        exit;
    }

    public function print($orderId)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($orderId);
        
        // Check if user owns this order or is admin
        if (!isAdmin() && $order->user_id !== $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to print this order.');
            redirect('orders');
        }
        
        return view('orders.print', compact('order'));
    }

    public function search()
    {
        $query = $_GET['query'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $orders = Order::with(['items.product', 'user']);

        // If not admin, only show user's orders
        if (!isAdmin()) {
            $orders->where('user_id', $_SESSION['user_id']);
        }

        if ($query) {
            $orders->whereHas('user', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orWhere('id', 'LIKE', "%{$query}%");
        }

        if ($status) {
            $orders->where('status', $status);
        }

        if ($dateFrom) {
            $orders->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $orders->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $orders->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function statistics()
    {
        // Only admin can view statistics
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to view statistics.');
            redirect('orders');
        }

        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $monthlyRevenue = Order::where('status', 'completed')
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->sum('total_amount');

        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        return view('orders.statistics', compact(
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'monthlyRevenue',
            'topProducts'
        ));
    }
} 