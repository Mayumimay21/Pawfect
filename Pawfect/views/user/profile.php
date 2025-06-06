<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header gradient-bg text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo BASE_URL . $user['avatar']; ?>" alt="Avatar" class="rounded-circle mb-3" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid #FFD700;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                    <?php endif; ?>
                    <h5><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                    <p class="text-muted"><?php echo $user['email']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
                
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliveryAddressModal">
                <i class="fas fa-map-marker-alt"></i> Manage Delivery Address
            </button>
            </div>
            
            <!-- Orders Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Orders</h5>
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <small class="text-muted">Showing 5 orders per page</small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p class="text-muted">No orders yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">Order ID</th>
                                        <th style="width: 15%">Total</th>
                                        <th style="width: 15%">Status</th>
                                        <th style="width: 40%">Items</th>
                                        <th style="width: 15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['status'] === 'delivered' ? 'success' : 
                                                    ($order['status'] === 'pending' ? 'warning' : ($order['status'] === 'shipped' ? 'info' : 'primary')); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; margin-right: 4px;">
                                                        <small class="text-truncate" style="max-width: 200px;" title="<?php echo $item['name']; ?>">
                                                            <?php echo $item['name']; ?> x<?php echo $item['quantity']; ?>
                                                        </small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group gap-2">
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewOrderModal<?php echo $order['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <nav aria-label="User orders pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/profile?page=<?php echo $currentPage - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/profile?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/profile?page=<?php echo $currentPage + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancelled Orders Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cancelled Orders</h5>
                    <?php if (isset($cancelledTotalPages) && $cancelledTotalPages > 1): ?>
                        <small class="text-muted">Showing 5 orders per page</small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($cancelledOrders)): ?>
                        <p class="text-muted">No cancelled orders.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">Order ID</th>
                                        <th style="width: 15%">Total</th>
                                        <th style="width: 15%">Status</th>
                                        <th style="width: 40%">Items</th>
                                        <th style="width: 15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cancelledOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; margin-right: 4px;">
                                                        <small class="text-truncate" style="max-width: 200px;" title="<?php echo $item['name']; ?>">
                                                            <?php echo $item['name']; ?> x<?php echo $item['quantity']; ?>
                                                        </small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group gap-2">
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewOrderModal<?php echo $order['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination for Cancelled Orders -->
                        <?php if (isset($cancelledTotalPages) && $cancelledTotalPages > 1): ?>
                        <nav aria-label="Cancelled orders pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($cancelledCurrentPage <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/profile?cancelled_page=<?php echo $cancelledCurrentPage - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $cancelledTotalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $cancelledCurrentPage) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/profile?cancelled_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($cancelledCurrentPage >= $cancelledTotalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/profile?cancelled_page=<?php echo $cancelledCurrentPage + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Modals -->
<?php foreach ($orders as $order): ?>
    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order #<?php echo $order['id']; ?> Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order Date:</strong> <?php echo $order['order_date'] ? date('M d, Y', strtotime($order['order_date'])) : '-'; ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'delivered' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : ($order['status'] === 'shipped' ? 'info' : 'primary')); 
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Delivery Address</h6>
                            <?php if (!empty($order['delivery_address'])): ?>
                                <?php $addr = $order['delivery_address']; ?>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['street']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['city'] . ', ' . $addr['barangay']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['zipcode']); ?></p>
                            <?php else: ?>
                                <p class="text-muted">No delivery address provided</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px;">
                                                <?php echo $item['name']; ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Order Modal -->
    <?php if ($order['status'] === 'pending'): ?>
    <div class="modal fade" id="cancelOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order #<?php echo $order['id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order?</p>
                    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="action" value="cancel_order">
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                            <button type="submit" class="btn btn-danger">Yes, Cancel Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Cancelled Order Modals -->
<?php foreach ($cancelledOrders as $order): ?>
    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order #<?php echo $order['id']; ?> Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order Date:</strong> <?php echo $order['order_date'] ? date('M d, Y', strtotime($order['order_date'])) : '-'; ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-danger">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Delivery Address</h6>
                            <?php if (!empty($order['delivery_address'])): ?>
                                <?php $addr = $order['delivery_address']; ?>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['street']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['city'] . ', ' . $addr['barangay']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($addr['zipcode']); ?></p>
                            <?php else: ?>
                                <p class="text-muted">No delivery address provided</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px;">
                                                <?php echo $item['name']; ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
.pagination .page-link {
    border-radius: 8px;
    border: none;
    color: #FF8C00;
}

.pagination .page-link:hover {
    background: #FF8C00;
    color: white;
}

.pagination .page-item.active .page-link {
    background: #FF8C00;
    border-color: #FF8C00;
}

.modal {
    backdrop-filter: blur(0.5px);
}

.modal-content {
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}
</style>

<!-- Delivery Address Modal -->
<div class="modal fade" id="deliveryAddressModal" tabindex="-1" aria-labelledby="deliveryAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryAddressModalLabel">Delivery Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/profile/update-address">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars(isset($delivery_address['city']) ? $delivery_address['city'] : ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="barangay" class="form-label">Barangay</label>
                        <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars(isset($delivery_address['barangay']) ? $delivery_address['barangay'] : ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="street" class="form-label">Street</label>
                        <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars(isset($delivery_address['street']) ? $delivery_address['street'] : ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="zipcode" class="form-label">Zipcode</label>
                        <input type="text" class="form-control" id="zipcode" name="zipcode" value="<?php echo htmlspecialchars(isset($delivery_address['zipcode']) ? $delivery_address['zipcode'] : ''); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
