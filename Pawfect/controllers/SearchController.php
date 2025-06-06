<?php

require_once 'models/Pet.php';
require_once 'models/Product.php';
require_once 'core/Helpers.php'; // Assuming search helper functions are here

class SearchController extends Controller {
    public function index() {
        // This method will handle the search request

        $query = $_GET['q'] ?? '';
        $results = [];

        if (strlen($query) >= 2) {
            // Perform search using helper functions
            $productResults = searchProducts($query, 10); // Limit results
            $petResults = searchPets($query, 10); // Limit results

            // Format results for the frontend
            foreach ($productResults as $product) {
                $results[] = [
                    'title' => $product['name'],
                    'url' => BASE_URL . '/product/' . $product['id'],
                    'image' => BASE_URL . $product['product_image'],
                    'description' => substr($product['description'], 0, 100) . '...', // Shorten description
                    'price' => '₱' . number_format($product['price'], 2),
                    'type' => 'Product'
                ];
            }

            foreach ($petResults as $pet) {
                 $results[] = [
                    'title' => $pet['name'],
                    'url' => BASE_URL . '/pet/' . $pet['id'],
                    'image' => BASE_URL . $pet['pet_image'],
                    'description' => $pet['breed'] . ', ' . $pet['age'] . ' years old', // Use breed and age as description
                    'price' => '', // Pets don't have a price
                    'type' => 'Pet'
                ];
            }
        }

        // Return results as JSON
        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }
}

?> 