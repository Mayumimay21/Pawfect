<?php
require_once 'models/Pet.php';
require_once 'models/Product.php';

class HomeController extends Controller {
    public function index() {
        $petModel = new Pet();
        $productModel = new Product();
        
        $featuredPets = array_slice($petModel->getAll(), 0, 6);
        $featuredProducts = array_slice($productModel->getAll(), 0, 8);
        
        $this->view('home', [
            'featuredPets' => $featuredPets,
            'featuredProducts' => $featuredProducts
        ]);
    }
}
?>
