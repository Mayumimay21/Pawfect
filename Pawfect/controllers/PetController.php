<?php
require_once 'models/Pet.php';
require_once 'core/Helpers.php'; // Include Helpers for search functions

class PetController extends Controller {
    public function index() {
        $petModel = new Pet();
        
        // Server-side pagination and filtering
        $limit = 9; // Number of pets per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters from GET request
        $query = isset($_GET['query']) ? trim($_GET['query']) : null;
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $gender = isset($_GET['gender']) ? trim($_GET['gender']) : null;
        $minAge = isset($_GET['minAge']) && $_GET['minAge'] !== '' ? (int)$_GET['minAge'] : null;
        $maxAge = isset($_GET['maxAge']) && $_GET['maxAge'] !== '' ? (int)$_GET['maxAge'] : null;
        
        error_log("PetController index - Parameters: query=$query, type=$type, gender=$gender, minAge=$minAge, maxAge=$maxAge");
        
        // Fetch paginated and filtered pets
        $pets = $petModel->getPaginated($limit, $offset, $type, $gender, null, $minAge, $maxAge, $query);
        $totalPets = $petModel->getTotalCount($type, $gender, null, $minAge, $maxAge, $query);
        
        // Initialize in_pawket as false for all pets
        foreach ($pets as &$pet) {
            $pet['in_pawket'] = false;
        }
        
        // Update pawket status for each pet if user is logged in
        if (isset($_SESSION['user_id'])) {
            require_once 'models/Pawket.php';
            $pawketModel = new Pawket();
            foreach ($pets as &$pet) {
                $pet['in_pawket'] = $pawketModel->isInPawket($_SESSION['user_id'], $pet['id']);
            }
        }
        
        error_log("PetController index - Total pets: $totalPets");
        
        // Calculate total pages
        $totalPages = ceil($totalPets / $limit);
        
        // Pass data to the view
        $this->view('pets/index', [
            'pets' => $pets,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'filterQuery' => $query,
            'filterType' => $type,
            'filterGender' => $gender,
            'filterMinAge' => $minAge,
            'filterMaxAge' => $maxAge,
            'pageTitle' => 'Adopt Pets'
        ]);
    }
    
    public function show($id) {
        $petModel = new Pet();
        $pet = $petModel->getById($id);
        
        if (!$pet) {
            $this->redirect('/pets');
            return;
        }
        
        // Initialize in_pawket as false by default
        $pet['in_pawket'] = false;
        
        // Check pawket status if user is logged in
        if (isset($_SESSION['user_id'])) {
            require_once 'models/Pawket.php';
            $pawketModel = new Pawket();
            $pet['in_pawket'] = $pawketModel->isInPawket($_SESSION['user_id'], $pet['id']);
        }
        
        $this->view('pets/show', [
            'pet' => $pet,
            'pageTitle' => $pet['name'] . ' - Pet Details'
        ]);
    }
    
    public function adopt() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $petId = $_POST['pet_id'];
            $userId = $_SESSION['user_id'];
            
            // Get pet details
            $petModel = new Pet();
            $pet = $petModel->getById($petId);
            
            if (!$pet) {
                setFlashMessage('error', 'Pet not found.');
                $this->redirect('/pets');
                return;
            }
            
            // Create pet order
            $petOrderModel = new PetOrder();
            $orderId = $petOrderModel->createOrder(
                $userId,
                $petId,
                $pet['price'],
                'COD' // Default payment method
            );
            
            if ($orderId) {
                setFlashMessage('success', 'Adoption request submitted successfully!');
                $this->redirect('/pet-orders/show/' . $orderId);
            } else {
                setFlashMessage('error', 'Failed to submit adoption request.');
                $this->redirect('/pet/' . $petId);
            }
        }
    }
    
    public function adoptedPets() {
        $petModel = new Pet();
        $adoptedPets = $petModel->getAdoptedPets();
        
        $this->view('pets/adopted', [
            'adoptedPets' => $adoptedPets,
            'pageTitle' => 'Adopted Pets'
        ]);
    }
}
?>
