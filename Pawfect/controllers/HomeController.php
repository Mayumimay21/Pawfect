<?php
require_once 'models/Pet.php';
require_once 'models/Product.php';

class HomeController extends Controller {
    public function index() {
        $petModel = new Pet();
        $productModel = new Product();
        
        $featuredPets = array_slice($petModel->getAll(), 0, 6);
        $featuredProducts = $productModel->getTopSoldProducts(4);
        
        $this->view('home', [
            'featuredPets' => $featuredPets,
            'featuredProducts' => $featuredProducts,
            'pageTitle' => 'Home'
        ]);
    }
}
?>
