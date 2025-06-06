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
                    <h5 class="mb-0">Filter Pawducts</h5>
                </div>
                <div class="card-body" style="background-color: #fff8e1;">
                    <form method="GET" action="<?php echo BASE_URL; ?>/pawducts">
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Search</label>
                            <input type="text" name="query" class="form-control" placeholder="Search pawducts..." style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterQuery ?? ''); ?>">
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
                            <a href="<?php echo BASE_URL; ?>/pawducts" class="btn btn-outline-secondary w-100 mt-2" style="background-color: #FFD700; color: #333; border-color: #FFD700;">Reset Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
    <div class="text-center mb-5">
                <h1 class="fw-bold" style="color: #FF8C00; font-family: 'Quicksand', Nunito, sans-serif;">Pet Pawducts</h1>
        <p class="lead text-muted">Everything your furry friends need to stay happy and healthy</p>
    </div>
    
            <!-- Products Grid -->
            <div class="row" id="productsGrid">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No pawducts found</h3>
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
                                            <button class="btn btn-primary add-to-cart rounded-pill" 
                                                    data-id="<?php echo htmlspecialchars($product['id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>">
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
                        $pagination_base_url = BASE_URL . '/pawducts?';
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

<!-- Add to Cart Confirmation Modal -->
<div class="modal fade" id="productListAddToCartModal" tabindex="-1" aria-labelledby="productListAddToCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productListAddToCartModalLabel">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add <span id="productListProductName" class="fw-bold"></span> to your cart?</p>
                <div class="mb-3">
                    <label for="productListQuantity" class="form-label">Quantity:</label>
                    <input type="number" class="form-control" id="productListQuantity" min="1" value="1">
                    <small class="text-muted">Available stock: <span id="productListAvailableStock"></span></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmProductListAddToCart()">
                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Added to Cart Success Modal -->
<div id="addedToCartModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog" style="margin: 15% auto; background-color: #fefefe; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h5>Item Added to Cart!</h5>
            <button type="button" onclick="closeModal('addedToCartModal')" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body text-center" style="margin-bottom: 15px;">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p>The product has been successfully added to your shopping cart.</p>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: center; gap: 10px;">
            <a href="<?php echo BASE_URL; ?>/cart" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> View Cart</a>
            <button type="button" class="btn btn-secondary" onclick="closeModal('addedToCartModal')">Continue Shopping</button>
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

.modal-custom {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.modal-body {
    margin-bottom: 15px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    color: #666;
}

.close-modal:hover {
    color: #000;
}
</style>

<script>
let currentProductId = null;
let currentMaxStock = 0;

function showProductListAddToCartModal(productId, productName, stockQuantity) {
    currentProductId = productId;
    currentMaxStock = stockQuantity;
    
    // Update modal content
    document.getElementById('productListProductName').textContent = productName;
    document.getElementById('productListAvailableStock').textContent = stockQuantity;
    document.getElementById('productListQuantity').max = stockQuantity;
    document.getElementById('productListQuantity').value = 1;
    
    // Show the modal using Bootstrap's Modal class
    const modalElement = document.getElementById('productListAddToCartModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function confirmProductListAddToCart() {
    if (!currentProductId) return;

    const quantity = parseInt(document.getElementById('productListQuantity').value);
    if (quantity < 1 || quantity > currentMaxStock) {
        alert('Please enter a valid quantity');
        return;
    }

    const formData = new FormData();
    formData.append('product_id', currentProductId);
    formData.append('quantity', quantity);

    // Hide the add to cart modal first
    const addToCartModal = bootstrap.Modal.getInstance(document.getElementById('productListAddToCartModal'));
    if (addToCartModal) {
        addToCartModal.hide();
    }

    fetch('<?php echo BASE_URL; ?>/cart/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count if it exists
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.cart_count;
                cartCount.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }
            
            // Show success message using the alert modal
            const alertModalElement = document.getElementById('alertModal');
            const alertModal = new bootstrap.Modal(alertModalElement);
            document.getElementById('alertModalBody').innerHTML = '<div class="alert alert-success mb-0">Item successfully added to cart!</div>';
            alertModal.show();
        } else {
            // Show error message using the alert modal
            const alertModalElement = document.getElementById('alertModal');
            const alertModal = new bootstrap.Modal(alertModalElement);
            document.getElementById('alertModalBody').innerHTML = '<div class="alert alert-danger mb-0">' + data.message + '</div>';
            alertModal.show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message using the alert modal
        const alertModalElement = document.getElementById('alertModal');
        const alertModal = new bootstrap.Modal(alertModalElement);
        document.getElementById('alertModalBody').innerHTML = '<div class="alert alert-danger mb-0">An error occurred while adding the item to your cart.</div>';
        alertModal.show();
    });
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get all add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    // Add click event listeners to all add to cart buttons
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const stockText = this.closest('.card').querySelector('.text-muted').textContent;
            const stockMatch = stockText.match(/Stock: (\d+)/);
            const stockQuantity = stockMatch ? parseInt(stockMatch[1]) : 0;
            
            showProductListAddToCartModal(productId, productName, stockQuantity);
        });
    });

    // Handle quantity input validation
    const quantityInput = document.getElementById('productListQuantity');
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value < 1) {
                this.value = 1;
            } else if (value > currentMaxStock) {
                this.value = currentMaxStock;
            }
        });
    }
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
