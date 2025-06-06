<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-2">
            <?php require_once 'views/layout/admin_sidebar.php'; ?>
        </div>
        
        <div class="col-md-10">
            <h1 class="fw-bold mb-4">Admin Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white" style="background: var(--primary-color);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $petStats['total']; ?></h4>
                                    <p class="mb-0">Total Pets</p>
                                </div>
                                <i class="fas fa-paw fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $petStats['adopted']; ?></h4>
                                    <p class="mb-0">Adopted Pets</p>
                                </div>
                                <i class="fas fa-heart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $productStats['total']; ?></h4>
                                    <p class="mb-0">Products</p>
                                </div>
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>$<?php echo number_format($orderStats['total_revenue'], 2); ?></h4>
                                    <p class="mb-0">Total Revenue</p>
                                </div>
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Pet Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h3 class="text-primary"><?php echo $petStats['dogs']; ?></h3>
                                    <p class="text-muted">Dogs</p>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-primary"><?php echo $petStats['cats']; ?></h3>
                                    <p class="text-muted">Cats</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h3 class="text-warning"><?php echo $orderStats['pending_orders']; ?></h3>
                                    <p class="text-muted">Pending</p>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-success"><?php echo $orderStats['delivered_orders']; ?></h3>
                                    <p class="text-muted">Delivered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Top Sold Pawducts</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Pawduct</th>
                                            <th>Sold Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topSoldProducts as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo $product['total_sold']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title text-danger">Pawducts Running Low!</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Pawduct</th>
                                            <th>Stock Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topOutOfStockProducts as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
