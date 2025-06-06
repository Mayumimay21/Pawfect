<?php
/**
 * Pawfect Pet Shop - Product Controller
 * Handles product listing, details, and management
 */

require_once 'models/Product.php';
require_once 'core/Helpers.php'; // Include Helpers for search functions

class ProductController extends Controller {
    
    public function index() {
        $productModel = new Product();
        
        // Server-side pagination and filtering
        $limit = 9; // Number of products per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters from GET request
        $query = isset($_GET['query']) ? trim($_GET['query']) : null;
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $minPrice = isset($_GET['minPrice']) && $_GET['minPrice'] !== '' ? (float)$_GET['minPrice'] : null;
        $maxPrice = isset($_GET['maxPrice']) && $_GET['maxPrice'] !== '' ? (float)$_GET['maxPrice'] : null;
        $stockStatus = isset($_GET['stockStatus']) ? trim($_GET['stockStatus']) : null;
        
        // Fetch paginated and filtered products
        $products = $productModel->getPaginated($limit, $offset, $type, $minPrice, $maxPrice, $query, $stockStatus);
        $totalProducts = $productModel->getTotalCount($type, $minPrice, $maxPrice, $query, $stockStatus);
        
        // Calculate total pages
        $totalPages = ceil($totalProducts / $limit);
        
        // Pass data to the view
        $this->view('products/index', [
            'products' => $products,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'filterQuery' => $query,
            'filterType' => $type,
            'filterMinPrice' => $minPrice,
            'filterMaxPrice' => $maxPrice,
            'filterStockStatus' => $stockStatus,
            'pageTitle' => 'Pawducts'
        ]);
    }
    
    public function show($id) {
        $productModel = new Product();
        $product = $productModel->getById($id);
        
        if (!$product) {
            $this->redirect('/products');
            return;
        }
        
        $this->view('products/show', [
            'product' => $product
        ]);
    }
}
?>
