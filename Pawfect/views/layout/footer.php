<footer class="gradient-bg text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Pawfect Pet Shop</h5>
                <p>Your trusted partner in pet care and adoption. We connect loving families with adorable pets.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>/pets" class="text-white-50">Adopt Pets</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/products" class="text-white-50">Pet Products</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/adopted-pets" class="text-white-50">Success Stories</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Info</h5>
                <p class="text-white-50">
                    <i class="fas fa-envelope"></i> info@pawfect.com<br>
                    <i class="fas fa-phone"></i> (555) 123-4567<br>
                    <i class="fas fa-map-marker-alt"></i> 123 Pet Street, City
                </p>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center">
            <p>&copy; 2024 Pawfect Pet Shop. All rights reserved.</p>
        </div>
    </div>
</footer>

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

<!-- Adopt Confirmation Modal -->
<div class="modal fade" id="adoptConfirmModal" tabindex="-1" aria-labelledby="adoptConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adoptConfirmModalLabel">Confirm Adoption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to express interest in adopting <span id="adoptPetName">this pet</span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="confirmAdoptBtn">Confirm Adoption</button>
      </div>
    </div>
  </div>
</div>

<!-- Generic Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="alertModalBody">
        <!-- Alert message will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize Bootstrap components when the DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            new bootstrap.Modal(modal);
        });

        // Check for and display session messages in the alert modal
        const successMessage = document.body.getAttribute('data-session-success');
        const errorMessage = document.body.getAttribute('data-session-error');

        if (successMessage) {
            showAlertModal(successMessage, 'success'); // Use alert modal for success too
            // Clear the session variable after displaying
            <?php unset($_SESSION['success']); ?>
        } else if (errorMessage) {
            showAlertModal(errorMessage, 'danger');
            // Clear the session variable after displaying
            <?php unset($_SESSION['error']); ?>
        }
    });

    function addToCart(productId) {
        fetch('<?php echo BASE_URL; ?>/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show the added to cart modal
                    const modalElement = document.getElementById('addedToCartModal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    
                    // Update cart count if it exists
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                        cartCount.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
                    }
                } else {
                    // Show error in alert modal
                    const modalElement = document.getElementById('alertModal');
                    const modal = new bootstrap.Modal(modalElement);
                    document.getElementById('alertModalBody').innerHTML = '<div class="alert alert-danger mb-0">' + data.message + '</div>';
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const modalElement = document.getElementById('alertModal');
                const modal = new bootstrap.Modal(modalElement);
                document.getElementById('alertModalBody').innerHTML = '<div class="alert alert-danger mb-0">An error occurred while adding the product to your cart.</div>';
                modal.show();
            });
    }

    function adoptPet(petId, petName) {
        const modalElement = document.getElementById('adoptConfirmModal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Set the pet name in the modal
        const petNameSpan = document.getElementById('adoptPetName');
        if (petNameSpan) {
            petNameSpan.textContent = petName;
        }

        // Set up the confirmation button handler
        const confirmBtn = document.getElementById('confirmAdoptBtn');
        if (confirmBtn) {
            confirmBtn.onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo BASE_URL; ?>/pets/adopt';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'pet_id';
                input.value = petId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };
        }

        modal.show();
    }

    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch('<?php echo BASE_URL; ?>/search?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            // Modified: Instead of displaying dropdown, redirect based on results
                            if (data.results && data.results.length > 0) {
                                // Check if there are product results
                                const hasProducts = data.results.some(result => result.type === 'Product');
                                // Check if there are pet results
                                const hasPets = data.results.some(result => result.type === 'Pet');

                                if (hasProducts) {
                                    // Redirect to products page with query
                                    window.location.href = '<?php echo BASE_URL; ?>/products?q=' + encodeURIComponent(query);
                                } else if (hasPets) {
                                     // Redirect to pets page with query
                                    window.location.href = '<?php echo BASE_URL; ?>/pets?q=' + encodeURIComponent(query);
                                } else {
                                    // Should not happen if results.length > 0 and types are Product/Pet, but as a fallback
                                    searchResults.innerHTML = '<div class="p-3 text-muted">No results found</div>';
                                    searchResults.style.display = 'block';
                                }

                            } else {
                                // No results found
                                searchResults.innerHTML = '<div class="p-3 text-muted">No results found</div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            searchResults.style.display = 'none';
                        });
                }, 300);
            });

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
    });

    // Generic Alert Modal function
    function showAlertModal(message, type = 'info') {
        const modalElement = document.getElementById('alertModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('alertModalBody');
            if (modalBody) {
                 // Use Bootstrap alert classes for styling within the modal body
                 modalBody.innerHTML = '<div class="alert alert-' + type + ' mb-0">' + message + '</div>';
            }
             const modalTitle = document.getElementById('alertModalLabel');
             if(modalTitle) {
                 modalTitle.textContent = type.charAt(0).toUpperCase() + type.slice(1);
             }
            modal.show();
        }
    }
</script>
</html>