<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliveryAddressModal">
    <i class="fas fa-map-marker-alt"></i> Manage Delivery Address
</button>

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
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label for="barangay" class="form-label">Barangay</label>
                        <input type="text" class="form-control" id="barangay" name="barangay" required>
                    </div>
                    <div class="mb-3">
                        <label for="street" class="form-label">Street</label>
                        <input type="text" class="form-control" id="street" name="street" required>
                    </div>
                    <div class="mb-3">
                        <label for="zipcode" class="form-label">Zipcode</label>
                        <input type="text" class="form-control" id="zipcode" name="zipcode" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="table-responsive mt-4">
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Total</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Shipped Date</th>
                <th>Delivery Address</th>
                <th>Payment</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <span class="badge bg-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'pending' ? 'warning' : ($order['status'] === 'shipped' ? 'info' : 'primary')); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
                <td><?php echo $order['order_date'] ? date('M d, Y', strtotime($order['order_date'])) : '-'; ?></td>
                <td><?php echo $order['shipped_date'] ? date('M d, Y', strtotime($order['shipped_date'])) : '-'; ?></td>
                <td>
                    <?php if (!empty($order['delivery_address'])): ?>
                        <?php $addr = $order['delivery_address']; ?>
                        <small><?php echo htmlspecialchars($addr['street']) . ', '; ?>
                            <?php echo htmlspecialchars($addr['city']) . ', '; ?>
                            <?php echo htmlspecialchars($addr['barangay']) . ' '; ?>
                            <?php echo htmlspecialchars($addr['zipcode']); ?></small>
                    <?php else: ?>
                        <small>-</small>
                    <?php endif; ?>
                </td>
                <td><?php echo $order['payment_method'] ?? '-'; ?></td>
                <td>
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; margin-right: 8px;">
                            <span><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 