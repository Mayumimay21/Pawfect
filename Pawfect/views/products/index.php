<?php
$page_title = 'Products - ' . get_setting('site_name', 'Pawfect Pet Shop');

// Capture current filter parameters for pagination links
$filter_params = http_build_query(array_filter([
    'query' => $filterQuery,
    'type' => $filterType,
    'minPrice' => $filterMinPrice,
    'maxPrice' => $filterMaxPrice
]));
?>

<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Sidebar with Filters -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #FF8C00; color: white;">
                    <h5 class="mb-0">Filter Products</h5>
                </div>
                <div class="card-body" style="background-color: #fff8e1;">
                    <form method="GET" action="<?php echo BASE_URL; ?>/products">
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Search</label>
                            <input type="text" name="query" class="form-control" placeholder="Search products..." style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterQuery ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Category</label>
                            <select name="type" class="form-select" style="border-color: #FFD700; color: #333;">
                                <option value="">All Categories</option>
                                <option value="foods" <?php echo ($filterType === 'foods') ? 'selected' : ''; ?>>Pet Foods</option>
                                <option value="accessories" <?php echo ($filterType === 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Price Range</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" name="minPrice" class="form-control" placeholder="Min Price (₱)" step="0.01" style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterMinPrice ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <input type="number" name="maxPrice" class="form-control" placeholder="Max Price (₱)" step="0.01" style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterMaxPrice ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Stock Status</label>
                            <select name="stockStatus" class="form-select" style="border-color: #FFD700; color: #333;">
                                <option value="">All</option>
                                <option value="in_stock">In Stock</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #FF8C00; border-color: #FF8C00;">Apply Filters</button>
                        <?php if (!empty($filterQuery) || !empty($filterType) || !empty($filterMinPrice) || !empty($filterMaxPrice)): ?>
                            <a href="<?php echo BASE_URL; ?>/products" class="btn btn-outline-secondary w-100 mt-2" style="background-color: #FFD700; color: #333; border-color: #FFD700;">Reset Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
    <div class="text-center mb-5">
                <h1 class="fw-bold" style="color: #FF8C00; font-family: 'Quicksand', Nunito, sans-serif;">Pet Products</h1>
        <p class="lead text-muted">Everything your furry friends need to stay happy and healthy</p>
    </div>
    
            <!-- Products Grid -->
            <div class="row" id="productsGrid">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No products found</h3>
                        <p class="text-muted">Try adjusting your filters</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card product-card h-100 shadow rounded-4 border-0" style="background: #fff8e1;">
                                <img src="<?php echo BASE_URL.$product['product_image']; ?>" class="card-img-top rounded-circle mx-auto mt-3" 
                                     style="height: 180px; width: 180px; object-fit: cover; border: 4px solid #FFD700; background: #fff;" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title fw-bold" style="color: #FF8C00; letter-spacing: 1px;">
                                        🛒 <?php echo htmlspecialchars($product['name']); ?>
                                    </h6>
                                    <p class="card-text">
                                        <span class="badge bg-secondary mb-2" style="background: #FFD700; color: #fff; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars(ucfirst($product['type'])); ?>
                                        </span><br>
                                        <strong class="text-primary fs-5">₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></strong><br>
                                        <small class="text-muted">
                                            Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?>
                                            <?php if ($product['stock_quantity'] === 0): ?>
                                                <span class="text-danger">(Out of Stock)</span>
                                            <?php elseif ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                                <span class="text-warning">(Low Stock)</span>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <?php if (isset($_SESSION['user_id']) && $product['stock_quantity'] > 0): ?>
                                            <button onclick="addToCart(<?php echo htmlspecialchars($product['id']); ?>)" class="btn btn-primary btn-sm rounded-pill fw-bold">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                                             <a href="<?php echo BASE_URL; ?>/login" class="btn btn-outline-primary btn-sm rounded-pill fw-bold">
                                                Login to Purchase
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm rounded-pill fw-bold" disabled>
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/product/<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Product pagination" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination">
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <?php
                        // Generate base URL for pagination links, keeping filters
                        $pagination_base_url = BASE_URL . '/products?';
                        $current_filters = [];
                        if (!empty($filterQuery)) $current_filters['query'] = urlencode($filterQuery);
                        if (!empty($filterType)) $current_filters['type'] = urlencode($filterType);
                        if (!empty($filterMinPrice)) $current_filters['minPrice'] = urlencode($filterMinPrice);
                        if (!empty($filterMaxPrice)) $current_filters['maxPrice'] = urlencode($filterMaxPrice);
                        $filter_string = http_build_query($current_filters);
                        ?>
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage - 1); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo (isset($currentPage) && $i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo (isset($currentPage) && $currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Added to Cart Modal -->
<div class="modal fade" id="addedToCartModal" tabindex="-1" aria-labelledby="addedToCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addedToCartModalLabel">Item Added to Cart!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>The product has been successfully added to your shopping cart.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="<?php echo BASE_URL; ?>/cart" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> View Cart</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-card:hover .card-img-top {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

.add-to-cart:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

.pagination .page-link {
    border-radius: 8px;
    margin: 0 2px;
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
</style>

<script>
// Simple function to handle reset button for the form
document.getElementById('resetFilters').addEventListener('click', () => {
    window.location.href = '<?php echo BASE_URL; ?>/products';
});

// addToCart function - Keep this if cart logic is still client-side (AJAX)
// If cart is purely server-side, this function should be removed and replaced with a form submission or link.
function addToCart(productId) {
    fetch('<?php echo BASE_URL; ?>/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Assuming showAlertModal is defined globally or in another included script
            // If not, it needs to be defined here or in a file included before this script block.
            showAlertModal('addedToCartModal');
            // Optional: Update cart count on the page
            // updateCartCount(); 
        } else {
            // Handle error, e.g., show an alert
            alert('Failed to add to cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        alert('An error occurred while adding to cart.');
    });
}

// Assuming showAlertModal is defined globally or in another included script
// If not, it needs to be defined here or in a file included before this script block.
// Example placeholder if not defined elsewhere:
/*
function showAlertModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}
*/

</script>

<?php require_once 'views/layout/footer.php'; ?>
